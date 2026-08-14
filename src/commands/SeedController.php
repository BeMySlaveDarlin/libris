<?php

declare(strict_types=1);

namespace app\commands;

use app\models\Author;
use app\models\Book;
use app\models\Genre;
use app\services\catalog\OpenLibraryClient;
use app\services\storage\FileStorageInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Connection;

final class SeedController extends Controller
{
    public int $books = 600;
    public bool $covers = true;
    public int $concurrency = 8;

    private const SUBJECTS = [
        'Фантастика' => 'science_fiction',
        'Фэнтези' => 'fantasy',
        'Детектив' => 'detective_and_mystery_stories',
        'Проза' => 'fiction',
        'Научпоп' => 'popular_science',
        'Историческая' => 'historical_fiction',
        'Приключения' => 'adventure_stories',
        'Триллер' => 'thriller',
        'Поэзия' => 'poetry',
        'Биография' => 'biography',
    ];

    private const DEMO_GENRES = ['Фантастика', 'Детектив', 'Проза', 'Поэзия'];

    private const DEMO_BOOKS = [
        ['Солярис', 1961, '9780156027601', ['Станислав Лем'], ['Фантастика']],
        ['Кибериада', 1965, '9780156027595', ['Станислав Лем'], ['Фантастика', 'Проза']],
        ['Левая рука Тьмы', 1969, '9780441478125', ['Урсула Ле Гуин'], ['Фантастика']],
        ['Обделённые', 1974, '9780060512750', ['Урсула Ле Гуин'], ['Фантастика', 'Проза']],
        ['Пикник на обочине', 1972, '9781613731727', ['Аркадий Стругацкий', 'Борис Стругацкий'], ['Фантастика']],
        ['Трудно быть богом', 1964, '9781613731598', ['Аркадий Стругацкий', 'Борис Стругацкий'], ['Фантастика']],
        ['Волны гасят ветер', 1985, '9781613731604', ['Аркадий Стругацкий', 'Борис Стругацкий'], ['Фантастика']],
        ['Звёздный десант', 1959, '9780441783588', ['Роберт Хайнлайн'], ['Фантастика']],
        ['Убийство в Восточном экспрессе', 1934, '9780007119318', ['Агата Кристи'], ['Детектив']],
        ['Десять негритят', 1939, '9780007136834', ['Агата Кристи'], ['Детектив']],
        ['Собака Баскервилей', 1902, '9780199536962', ['Артур Конан Дойл'], ['Детектив']],
        ['Записки о Шерлоке Холмсе', 1893, '9780199536955', ['Артур Конан Дойл'], ['Детектив', 'Проза']],
        ['Мастер и Маргарита', 1967, '9780143108276', ['Михаил Булгаков'], ['Проза']],
        ['Собачье сердце', 1925, '9780802150592', ['Михаил Булгаков'], ['Проза']],
        ['Стихотворения', 1837, null, ['Александр Пушкин'], ['Поэзия']],
        ['Евгений Онегин', 1833, '9780143039105', ['Александр Пушкин'], ['Поэзия', 'Проза']],
        ['Марсианские хроники', 1950, '9780062079930', ['Рэй Брэдбери'], ['Фантастика']],
        ['451 градус по Фаренгейту', 1953, '9781451673319', ['Рэй Брэдбери'], ['Фантастика', 'Проза']],
        ['Я, робот', 1950, '9780553294385', ['Айзек Азимов'], ['Фантастика']],
        ['Основание', 1951, '9780553293357', ['Айзек Азимов'], ['Фантастика']],
        ['Убийство Роджера Экройда', 1926, '9780007527526', ['Агата Кристи'], ['Детектив']],
        ['Этюд в багровых тонах', 1887, '9780199536948', ['Артур Конан Дойл'], ['Детектив']],
        ['Белая гвардия', 1925, '9780300189377', ['Михаил Булгаков'], ['Проза']],
        ['Медный всадник', 1837, null, ['Александр Пушкин'], ['Поэзия']],
        ['Дюна', 1965, '9780441013593', ['Фрэнк Герберт'], ['Фантастика']],
        ['Заповедник гоблинов', 1968, '9780881847925', ['Клиффорд Саймак'], ['Фантастика', 'Проза']],
    ];

    private const PAGE_SIZE = 100;
    private const MAX_PAGES = 5;
    private const MIN_COVER_BYTES = 2048;

    public function options($actionID): array
    {
        return ['books', 'covers', 'concurrency'];
    }

    /**
     * @return array<string, string>
     */
    public function optionAliases(): array
    {
        return ['b' => 'books', 'c' => 'covers'];
    }

    public function actionIndex(): int
    {
        $db = \Yii::$app->getDb();
        $client = new OpenLibraryClient(\Yii::$container->get(ClientInterface::class));

        $this->stdout('Загружаю каталог из Open Library…' . PHP_EOL);
        $catalogue = $this->fetchCatalogue($client);

        if ($catalogue === []) {
            $this->stderr('Open Library не отдал ни одной книги.' . PHP_EOL);

            return ExitCode::UNAVAILABLE;
        }

        $this->truncate($db);
        $genres = $this->storeGenres();
        [$stored, $withCover] = $this->storeBooks($db, $catalogue, $genres);

        if ($this->covers && $withCover !== []) {
            $this->stdout(sprintf('Скачиваю обложки: %d…%s', count($withCover), PHP_EOL));
            $this->stdout(sprintf('Обложек сохранено: %d.%s', $this->downloadCovers($client, $withCover), PHP_EOL));
        }

        $this->stdout(sprintf(
            'Готово: книг %d, авторов %d, жанров %d.%s',
            $stored,
            (int) Author::find()->count(),
            count($genres),
            PHP_EOL,
        ));

        return ExitCode::OK;
    }

    public function actionDemo(): int
    {
        $db = \Yii::$app->getDb();
        $this->truncate($db);

        $genres = [];
        foreach (self::DEMO_GENRES as $name) {
            $genre = new Genre(['name' => $name]);
            $genre->save();
            $genres[$name] = $genre;
        }

        $authors = [];
        $stored = 0;

        $db->transaction(function () use ($genres, &$authors, &$stored): void {
            foreach (self::DEMO_BOOKS as [$title, $year, $isbn, $names, $genreNames]) {
                $book = new Book(['title' => $title, 'year' => $year, 'isbn' => $isbn]);
                $book->description = sprintf('%s — издание %d года.', $title, $year);

                if (!$book->save()) {
                    continue;
                }

                foreach ($names as $name) {
                    $authors[$name] ??= $this->author($name);
                    $book->link('authors', $authors[$name]);
                }

                foreach ($genreNames as $genreName) {
                    $book->link('genres', $genres[$genreName]);
                }

                $stored++;
            }
        });

        $this->stdout(sprintf(
            'Демонстрационный набор: книг %d, авторов %d, жанров %d.%s',
            $stored,
            count($authors),
            count($genres),
            PHP_EOL,
        ));

        return ExitCode::OK;
    }

    /**
     * @return array<string, array{book: array<string, mixed>, genres: list<string>}>
     */
    private function fetchCatalogue(OpenLibraryClient $client): array
    {
        $perSubject = (int) ceil($this->books / count(self::SUBJECTS));
        $catalogue = [];

        foreach (self::SUBJECTS as $genre => $subject) {
            $collected = 0;
            for ($page = 1; $collected < $perSubject && $page <= self::MAX_PAGES; $page++) {
                $books = $client->booksBySubject($subject, self::PAGE_SIZE, $page);
                if ($books === []) {
                    break;
                }

                foreach ($books as $book) {
                    $key = mb_strtolower($book['title'] . '|' . $book['authors'][0]);

                    if (isset($catalogue[$key])) {
                        $catalogue[$key]['genres'][] = $genre;
                        continue;
                    }

                    if ($collected >= $perSubject) {
                        continue;
                    }

                    $catalogue[$key] = ['book' => $book, 'genres' => [$genre]];
                    $collected++;
                }
            }

            $this->stdout(sprintf('  %-14s %d%s', $genre, $collected, PHP_EOL));
        }

        return $catalogue;
    }

    private function truncate(Connection $db): void
    {
        $db->createCommand()->checkIntegrity(false)->execute();
        foreach (['sms_delivery', 'subscription', 'book_genre', 'book_author', 'book', 'author', 'genre'] as $table) {
            $db->createCommand()->truncateTable("{{%{$table}}}")->execute();
        }
        $db->createCommand()->checkIntegrity(true)->execute();
    }

    /**
     * @return array<string, Genre>
     */
    private function storeGenres(): array
    {
        $genres = [];
        foreach (array_keys(self::SUBJECTS) as $name) {
            $genre = new Genre(['name' => $name]);
            $genre->save();
            $genres[$name] = $genre;
        }

        return $genres;
    }

    /**
     * @param array<string, array{book: array<string, mixed>, genres: list<string>}> $catalogue
     * @param array<string, Genre> $genres
     * @return array{0: int, 1: list<array{id: int, coverId: int}>}
     */
    private function storeBooks(Connection $db, array $catalogue, array $genres): array
    {
        $authors = [];
        $stored = 0;
        $withCover = [];
        $usedIsbn = [];

        $db->transaction(function () use ($catalogue, $genres, &$authors, &$stored, &$withCover, &$usedIsbn): void {
            foreach ($catalogue as $entry) {
                $data = $entry['book'];

                $isbn = $data['isbn'];
                if ($isbn !== null && isset($usedIsbn[$isbn])) {
                    $isbn = null;
                }

                $book = new Book([
                    'title' => $data['title'],
                    'year' => $data['year'],
                    'isbn' => $isbn,
                    'description' => $data['description'],
                ]);

                if (!$book->save()) {
                    continue;
                }

                if ($isbn !== null) {
                    $usedIsbn[$isbn] = true;
                }

                $linked = 0;
                foreach (array_unique($data['authors']) as $name) {
                    if (!array_key_exists($name, $authors)) {
                        $authors[$name] = $this->author($name);
                    }

                    if ($authors[$name] === null) {
                        continue;
                    }

                    $book->link('authors', $authors[$name]);
                    $linked++;
                }

                if ($linked === 0) {
                    $book->delete();
                    continue;
                }

                foreach (array_unique($entry['genres']) as $genreName) {
                    $book->link('genres', $genres[$genreName]);
                }

                if ($data['coverId'] !== null) {
                    $withCover[] = ['id' => (int) $book->id, 'coverId' => (int) $data['coverId']];
                }

                $stored++;
            }
        });

        return [$stored, $withCover];
    }

    private function author(string $name): ?Author
    {
        $author = new Author(['full_name' => mb_substr($name, 0, 255)]);

        return $author->save() ? $author : null;
    }

    /**
     * @param list<array{id: int, coverId: int}> $targets
     */
    private function downloadCovers(OpenLibraryClient $client, array $targets): int
    {
        $http = \Yii::$container->get(ClientInterface::class);
        $storage = \Yii::$container->get(FileStorageInterface::class);
        $saved = 0;

        $requests = static function () use ($targets, $client): \Generator {
            foreach ($targets as $target) {
                yield $target['id'] => new Request('GET', $client->coverUrl($target['coverId']));
            }
        };

        $pool = new Pool($http, $requests(), [
            'concurrency' => $this->concurrency,
            'options' => ['timeout' => 20, 'http_errors' => false],
            'fulfilled' => function (ResponseInterface $response, int $bookId) use ($storage, &$saved): void {
                if ($response->getStatusCode() !== 200) {
                    return;
                }

                $body = (string) $response->getBody();
                if (strlen($body) < self::MIN_COVER_BYTES) {
                    return;
                }

                Book::updateAll(['cover_path' => $storage->saveContents($body, 'jpg')], ['id' => $bookId]);
                $saved++;

                if ($saved % 100 === 0) {
                    $this->stdout(sprintf('  скачано %d…%s', $saved, PHP_EOL));
                }
            },
            'rejected' => static fn(GuzzleException $reason, int $bookId): null => null,
        ]);

        $pool->promise()->wait();

        return $saved;
    }
}
