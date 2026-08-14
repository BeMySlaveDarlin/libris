<?php

declare(strict_types=1);

namespace app\models\forms;

final class AuthorSearch extends QueryFilter
{
    public string $q = '';
    /** @var list<int> */
    public array $genres = [];

    public function rules(): array
    {
        return [
            [['q'], 'trim'],
            [['q'], 'string', 'max' => 255],
            [['genres'], 'each', 'rule' => ['integer']],
        ];
    }


    public function attributeLabels(): array
    {
        return [
            'q' => 'Поиск',
            'genres' => 'Жанры',
        ];
    }

    public function isFiltered(): bool
    {
        return $this->q !== '' || $this->genres !== [];
    }
}
