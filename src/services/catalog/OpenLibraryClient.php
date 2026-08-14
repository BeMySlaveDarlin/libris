<?php

declare(strict_types=1);

namespace app\services\catalog;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Nicebooks\Isbn\Exception\IsbnException;
use Nicebooks\Isbn\Isbn;

final readonly class OpenLibraryClient
{
    private const SEARCH_URL = 'https://openlibrary.org/search.json';
    private const AUTHOR_SEARCH_URL = 'https://openlibrary.org/search/authors.json';
    private const AUTHOR_PHOTO_URL = 'https://covers.openlibrary.org/a/olid/%s-M.jpg';
    private const COVER_URL = 'https://covers.openlibrary.org/b/id/%d-L.jpg';
    private const FIELDS = 'title,author_name,first_publish_year,isbn,cover_i,first_sentence';

    public function __construct(
        private ClientInterface $http,
    ) {
    }

    /**
     * @return list<array{title: string, authors: list<string>, year: int, isbn: ?string, coverId: ?int, description: ?string}>
     */
    public function booksBySubject(string $subject, int $limit, int $page = 1): array
    {
        try {
            $response = $this->http->request('GET', self::SEARCH_URL, [
                'query' => [
                    'subject' => $subject,
                    'limit' => $limit,
                    'page' => $page,
                    'fields' => self::FIELDS,
                    'sort' => 'editions',
                ],
                'timeout' => 30,
                'http_errors' => false,
            ]);
        } catch (GuzzleException) {
            return [];
        }

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $payload = json_decode((string) $response->getBody(), true);
        if (!is_array($payload) || !isset($payload['docs']) || !is_array($payload['docs'])) {
            return [];
        }

        $books = [];
        foreach ($payload['docs'] as $doc) {
            $book = $this->normalise($doc);
            if ($book !== null) {
                $books[] = $book;
            }
        }

        return $books;
    }

    /**
     * @return array{key: string, birth: ?string, death: ?string, topWork: ?string, works: int, subjects: list<string>}|null
     */
    public function author(string $name): ?array
    {
        try {
            $response = $this->http->request('GET', self::AUTHOR_SEARCH_URL, [
                'query' => ['q' => $name, 'limit' => 1],
                'timeout' => 20,
                'http_errors' => false,
            ]);
        } catch (GuzzleException) {
            return null;
        }

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $payload = json_decode((string) $response->getBody(), true);
        $doc = is_array($payload) && is_array($payload['docs'] ?? null) ? ($payload['docs'][0] ?? null) : null;

        if (!is_array($doc) || !isset($doc['key'])) {
            return null;
        }

        return [
            'key' => (string) $doc['key'],
            'birth' => isset($doc['birth_date']) ? (string) $doc['birth_date'] : null,
            'death' => isset($doc['death_date']) ? (string) $doc['death_date'] : null,
            'topWork' => isset($doc['top_work']) ? (string) $doc['top_work'] : null,
            'works' => (int) ($doc['work_count'] ?? 0),
            'subjects' => array_slice(array_map(
                static fn(mixed $subject): string => (string) $subject,
                is_array($doc['top_subjects'] ?? null) ? $doc['top_subjects'] : [],
            ), 0, 4),
        ];
    }

    public function authorPhotoUrl(string $key): string
    {
        return sprintf(self::AUTHOR_PHOTO_URL, $key);
    }

    public function coverUrl(int $coverId): string
    {
        return sprintf(self::COVER_URL, $coverId);
    }

    /**
     * @param array<string, mixed> $doc
     * @return array{title: string, authors: list<string>, year: int, isbn: ?string, coverId: ?int, description: ?string}|null
     */
    private function normalise(array $doc): ?array
    {
        $title = trim((string) ($doc['title'] ?? ''));
        $authors = array_values(array_filter(array_map(
            static fn(mixed $name): string => trim((string) $name),
            is_array($doc['author_name'] ?? null) ? $doc['author_name'] : [],
        )));
        $year = (int) ($doc['first_publish_year'] ?? 0);

        if ($title === '' || $authors === [] || $year < 1450) {
            return null;
        }

        return [
            'title' => mb_substr($title, 0, 255),
            'authors' => array_slice($authors, 0, 3),
            'year' => $year,
            'isbn' => $this->firstValidIsbn(is_array($doc['isbn'] ?? null) ? $doc['isbn'] : []),
            'coverId' => isset($doc['cover_i']) ? (int) $doc['cover_i'] : null,
            'description' => $this->description($doc['first_sentence'] ?? null),
        ];
    }

    /**
     * @param list<mixed> $candidates
     */
    private function firstValidIsbn(array $candidates): ?string
    {
        foreach (array_slice($candidates, 0, 20) as $candidate) {
            try {
                return Isbn::of((string) $candidate)->to13()->toString();
            } catch (IsbnException) {
                continue;
            }
        }

        return null;
    }

    private function description(mixed $sentence): ?string
    {
        if (is_array($sentence)) {
            $sentence = $sentence[0] ?? null;
        }

        if (!is_string($sentence)) {
            return null;
        }

        $sentence = trim($sentence);

        return $sentence === '' ? null : mb_substr($sentence, 0, 1000);
    }
}
