<?php

declare(strict_types=1);

namespace app\models\query;

use app\models\Book;
use yii\db\ActiveQuery;

/**
 * @extends ActiveQuery<Book>
 */
final class BookQuery extends ActiveQuery
{
    public function publishedIn(int $year): self
    {
        return $this->andWhere(['year' => $year]);
    }

    public function byAuthor(int $authorId): self
    {
        return $this->innerJoin('{{%book_author}} ba', 'ba.book_id = {{%book}}.id')
            ->andWhere(['ba.author_id' => $authorId]);
    }

    public function newestFirst(): self
    {
        return $this->orderBy(['year' => SORT_DESC, 'title' => SORT_ASC]);
    }
}
