<?php

declare(strict_types=1);

namespace app\services\catalog;

use app\models\Book;
use app\models\SmsDelivery;
use app\models\Subscription;
use app\services\sms\SmsSenderInterface;
use yii\db\IntegrityException;
use yii\db\Query;

final readonly class SubscriberNotifier
{
    public function __construct(
        private SmsSenderInterface $sender,
    ) {
    }

    public function notifyAboutBook(Book $book): void
    {
        foreach ($this->subscriptions($book) as $subscription) {
            $delivery = $this->reserve($subscription, $book);
            if ($delivery === null) {
                continue;
            }

            $result = $this->sender->send($subscription->phone, $this->message($book));
            if ($result->success) {
                $delivery->markSent($result->messageId);
            } else {
                $delivery->markFailed((string) $result->error);
            }

            $delivery->save(false);
        }
    }

    /**
     * @return Subscription[]
     */
    private function subscriptions(Book $book): array
    {
        $authorIds = (new Query())
            ->select('author_id')
            ->from('{{%book_author}}')
            ->where(['book_id' => $book->id])
            ->column();

        if ($authorIds === []) {
            return [];
        }

        return Subscription::find()
            ->where(['author_id' => $authorIds])
            ->all();
    }

    private function reserve(Subscription $subscription, Book $book): ?SmsDelivery
    {
        $delivery = new SmsDelivery();
        $delivery->subscription_id = $subscription->id;
        $delivery->book_id = $book->id;
        $delivery->status = SmsDelivery::STATUS_PENDING;

        try {
            return $delivery->save() ? $delivery : null;
        } catch (IntegrityException) {
            return null;
        }
    }

    private function message(Book $book): string
    {
        $authors = implode(', ', array_map(
            static fn($author): string => $author->full_name,
            $book->authors,
        ));

        return sprintf('Новая книга: «%s» (%d). Автор: %s', $book->title, $book->year, $authors);
    }
}
