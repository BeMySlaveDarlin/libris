<?php

declare(strict_types=1);

namespace app\models\forms;

final class BookSearch extends QueryFilter
{
    public const SORTS = [
        'title' => 'Название: А–Я',
        '-title' => 'Название: Я–А',
        '-year' => 'Год: сначала новые',
        'year' => 'Год: сначала старые',
    ];

    public string $sort = 'title';
    public string $q = '';
    /** @var list<int> */
    public array $genres = [];
    public ?int $year = null;

    public function rules(): array
    {
        return [
            [['q'], 'trim'],
            [['q'], 'string', 'max' => 255],
            [['year'], 'integer'],
            [['genres'], 'each', 'rule' => ['integer']],
            [['sort'], 'in', 'range' => array_keys(self::SORTS)],
        ];
    }


    public function attributeLabels(): array
    {
        return [
            'q' => 'Поиск',
            'genres' => 'Жанры',
            'year' => 'Год',
            'sort' => 'Сортировка',
        ];
    }

    public function isFiltered(): bool
    {
        return $this->q !== '' || $this->genres !== [] || $this->year !== null || $this->sort !== 'title';
    }

    /**
     * @return array<string, int>
     */
    public function orderBy(): array
    {
        return match ($this->sort) {
            '-title' => ['{{%book}}.title' => SORT_DESC],
            'year' => ['{{%book}}.year' => SORT_ASC, '{{%book}}.title' => SORT_ASC],
            '-year' => ['{{%book}}.year' => SORT_DESC, '{{%book}}.title' => SORT_ASC],
            default => ['{{%book}}.title' => SORT_ASC],
        };
    }
}
