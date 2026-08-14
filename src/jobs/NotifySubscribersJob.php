<?php

declare(strict_types=1);

namespace app\jobs;

use app\models\Book;
use app\services\catalog\SubscriberNotifier;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

final class NotifySubscribersJob extends BaseObject implements JobInterface
{
    public function __construct(
        public int $bookId = 0,
        array $config = [],
    ) {
        parent::__construct($config);
    }

    public function execute($queue): void
    {
        $book = Book::find()->with('authors')->where(['id' => $this->bookId])->one();
        if ($book === null) {
            return;
        }

        \Yii::$container->get(SubscriberNotifier::class)->notifyAboutBook($book);
    }
}
