<?php

declare(strict_types=1);

namespace app\services\catalog;

use app\models\forms\ReportFilter;
use yii\db\Connection;
use yii\db\Query;

final readonly class ReportService
{
    public function __construct(
        private Connection $db,
        private int $defaultLimit,
    ) {
    }

    /**
     * @return list<array{author_id: int, full_name: string, books_count: int}>
     */
    public function topAuthors(ReportFilter $filter): array
    {
        $query = (new Query())
            ->select([
                'author_id' => 'a.id',
                'full_name' => 'a.full_name',
                'books_count' => 'COUNT(ba.book_id)',
            ])
            ->from(['ba' => '{{%book_author}}'])
            ->innerJoin(['b' => '{{%book}}'], 'b.id = ba.book_id')
            ->innerJoin(['a' => '{{%author}}'], 'a.id = ba.author_id')
            ->where(['b.year' => $filter->year])
            ->groupBy(['a.id', 'a.full_name'])
            ->orderBy(['books_count' => SORT_DESC, 'a.full_name' => SORT_ASC])
            ->limit($filter->limit ?: $this->defaultLimit);

        if ($filter->author !== '') {
            $query->andWhere(['like', 'a.full_name', $filter->author]);
        }

        if ($filter->genres !== []) {
            $query->innerJoin(['bg' => '{{%book_genre}}'], 'bg.book_id = b.id')
                ->andWhere(['bg.genre_id' => $filter->genres]);
        }

        if ($filter->minBooks !== null) {
            $query->having(['>=', 'COUNT(ba.book_id)', $filter->minBooks]);
        }

        return $query->all($this->db);
    }

    /**
     * @return list<int>
     */
    public function availableYears(): array
    {
        return array_map('intval', (new Query())
            ->select('year')
            ->distinct()
            ->from('{{%book}}')
            ->orderBy(['year' => SORT_DESC])
            ->column($this->db));
    }

    public function latestYear(): ?int
    {
        return $this->availableYears()[0] ?? null;
    }
}
