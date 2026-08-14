<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\Book;
use Carbon\CarbonImmutable;

final class ReportFilter extends QueryFilter
{
    public const LIMITS = [10, 25, 50];

    public ?int $year = null;
    public int $limit = 10;
    public string $author = '';
    public ?int $minBooks = null;
    /** @var list<int> */
    public array $genres = [];

    public function rules(): array
    {
        return [
            [['year'], 'integer', 'min' => Book::YEAR_MIN, 'max' => CarbonImmutable::now()->year],
            [['limit'], 'in', 'range' => self::LIMITS],
            [['minBooks'], 'integer', 'min' => 1, 'max' => 999],
            [['author'], 'trim'],
            [['author'], 'string', 'max' => 255],
            [['genres'], 'each', 'rule' => ['integer']],
        ];
    }


    public function attributeLabels(): array
    {
        return [
            'year' => 'Год',
            'limit' => 'Показать',
            'author' => 'Автор',
            'minBooks' => 'Книг не менее',
            'genres' => 'Жанры',
        ];
    }

    public function isFiltered(): bool
    {
        return $this->author !== '' || $this->minBooks !== null || $this->genres !== [] || $this->limit !== 10;
    }

    public function normalise(int $fallbackYear): void
    {
        if (!$this->validate()) {
            $this->year = null;
            $this->limit = 10;
            $this->minBooks = null;
            $this->author = '';
            $this->genres = [];
        }

        $this->year ??= $fallbackYear;
    }
}
