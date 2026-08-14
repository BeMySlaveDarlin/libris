<?php

declare(strict_types=1);

namespace app\models\query;

use app\models\Author;
use yii\db\ActiveQuery;

/**
 * @extends ActiveQuery<Author>
 */
final class AuthorQuery extends ActiveQuery
{
    public function byFullName(string $term): self
    {
        return $this->andWhere(['like', 'full_name', $term]);
    }

    public function orderedByName(): self
    {
        return $this->orderBy(['full_name' => SORT_ASC]);
    }
}
