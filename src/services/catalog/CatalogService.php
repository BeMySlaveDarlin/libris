<?php

declare(strict_types=1);

namespace app\services\catalog;

use app\models\Author;
use app\models\Book;
use app\models\forms\AuthorSearch;
use app\models\forms\BookSearch;
use app\models\Genre;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Connection;
use yii\web\NotFoundHttpException;

final readonly class CatalogService
{
    private const BOOKS_PER_PAGE = 20;
    private const AUTHORS_PER_PAGE = 30;

    public function __construct(
        private Connection $db,
    ) {
    }

    public function books(BookSearch $search): ActiveDataProvider
    {
        $query = Book::find()->with(['authors', 'genres']);

        $this->applyTerm($query, $search->q);
        $this->applyGenres($query, $search->genres);

        if ($search->year !== null) {
            $query->andWhere(['{{%book}}.year' => $search->year]);
        }

        return new ActiveDataProvider([
            'query' => $query->orderBy($search->orderBy()),
            'pagination' => ['pageSize' => self::BOOKS_PER_PAGE],
            'sort' => false,
        ]);
    }

    public function authors(AuthorSearch $search): ActiveDataProvider
    {
        $query = Author::find();

        if ($search->q !== '') {
            $query->byFullName($search->q);
        }

        if ($search->genres !== []) {
            $query->innerJoin('{{%book_author}} ba', 'ba.author_id = {{%author}}.id')
                ->innerJoin('{{%book_genre}} bg', 'bg.book_id = ba.book_id')
                ->andWhere(['bg.genre_id' => $search->genres])
                ->groupBy('{{%author}}.id');
        }

        return new ActiveDataProvider([
            'query' => $query->orderedByName(),
            'pagination' => ['pageSize' => self::AUTHORS_PER_PAGE],
            'sort' => false,
        ]);
    }

    /**
     * @return list<Genre>
     */
    public function genres(): array
    {
        return Genre::find()->orderBy(['name' => SORT_ASC])->all();
    }

    public function findBook(int $id): Book
    {
        $book = Book::find()->with(['authors', 'genres'])->where(['id' => $id])->one();
        if ($book === null) {
            throw new NotFoundHttpException('Книга не найдена.');
        }

        return $book;
    }

    public function findAuthor(int $id): Author
    {
        $author = Author::findOne(['id' => $id]);
        if ($author === null) {
            throw new NotFoundHttpException('Автор не найден.');
        }

        return $author;
    }

    /**
     * @return list<Book>
     */
    public function booksOfAuthor(Author $author): array
    {
        /** @var list<Book> $books */
        $books = $author->getBooks()
            ->with('genres')
            ->orderBy(['year' => SORT_DESC, 'title' => SORT_ASC])
            ->all();

        return $books;
    }

    public function saveAuthor(Author $author): bool
    {
        return $author->save();
    }

    public function deleteAuthor(Author $author): void
    {
        $author->delete();
    }

    private function applyTerm(ActiveQuery $query, string $term): void
    {
        if ($term === '') {
            return;
        }

        $byAuthor = $this->db->createCommand(
            'SELECT ba.book_id FROM {{%book_author}} ba
             INNER JOIN {{%author}} a ON a.id = ba.author_id
             WHERE a.full_name LIKE :term',
            [':term' => '%' . $term . '%'],
        )->queryColumn();

        $query->andWhere([
            'or',
            ['like', '{{%book}}.title', $term],
            ['like', '{{%book}}.description', $term],
            ['{{%book}}.id' => $byAuthor],
        ]);
    }

    /**
     * @param list<int> $genres
     */
    private function applyGenres(ActiveQuery $query, array $genres): void
    {
        if ($genres === []) {
            return;
        }

        $query->innerJoin('{{%book_genre}} bg', 'bg.book_id = {{%book}}.id')
            ->andWhere(['bg.genre_id' => $genres])
            ->groupBy('{{%book}}.id')
            ->having('COUNT(DISTINCT bg.genre_id) = :count', [':count' => count($genres)]);
    }
}
