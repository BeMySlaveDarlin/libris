<?php

declare(strict_types=1);

namespace app\services\catalog;

use app\jobs\NotifySubscribersJob;
use app\models\Author;
use app\models\Book;
use app\models\Genre;
use app\models\forms\BookForm;
use app\services\storage\FileStorageInterface;
use RuntimeException;
use yii\db\Connection;
use yii\queue\Queue;

final readonly class BookService
{
    public function __construct(
        private Connection $db,
        private FileStorageInterface $storage,
        private Queue $queue,
    ) {
    }

    public function create(BookForm $form): Book
    {
        $book = new Book();

        return $this->persist($book, $form, true);
    }

    public function update(Book $book, BookForm $form): Book
    {
        return $this->persist($book, $form, false);
    }

    public function delete(Book $book): void
    {
        $cover = $book->cover_path;

        $this->db->transaction(static function () use ($book): void {
            if ($book->delete() === false) {
                throw new RuntimeException('Не удалось удалить книгу.');
            }
        });

        $this->storage->delete($cover);
    }

    /**
     * @return list<Genre>
     */
    private function genresFor(BookForm $form): array
    {
        $genres = Genre::find()->where(['id' => $form->genreIds])->all();

        foreach ($form->newGenreNames() as $name) {
            $existing = Genre::findOne(['name' => $name]);
            if ($existing !== null) {
                $genres[] = $existing;
                continue;
            }

            $genre = new Genre(['name' => $name]);
            if ($genre->save()) {
                $genres[] = $genre;
            }
        }

        $unique = [];
        foreach ($genres as $genre) {
            $unique[(int) $genre->id] = $genre;
        }

        return array_values($unique);
    }

    private function persist(Book $book, BookForm $form, bool $isNew): Book
    {
        $previousCover = $book->cover_path;
        $uploadedCover = $form->cover !== null ? $this->storage->save($form->cover) : null;

        $book->title = $form->title;
        $book->year = $form->year;
        $book->description = $form->description;
        $book->isbn = $form->isbn;
        if ($uploadedCover !== null) {
            $book->cover_path = $uploadedCover;
        }

        $genres = $this->genresFor($form);

        try {
            $this->db->transaction(static function () use ($book, $form, $genres): void {
                if (!$book->save()) {
                    throw new RuntimeException('Не удалось сохранить книгу.');
                }

                $book->unlinkAll('authors', true);
                foreach (Author::find()->where(['id' => $form->authorIds])->all() as $author) {
                    $book->link('authors', $author);
                }

                $book->unlinkAll('genres', true);
                foreach ($genres as $genre) {
                    $book->link('genres', $genre);
                }
            });
        } catch (\Throwable $exception) {
            $this->storage->delete($uploadedCover);

            throw $exception;
        }

        if ($uploadedCover !== null) {
            $this->storage->delete($previousCover);
        }

        if ($isNew) {
            $this->queue->push(new NotifySubscribersJob(bookId: $book->id));
        }

        return $book;
    }
}
