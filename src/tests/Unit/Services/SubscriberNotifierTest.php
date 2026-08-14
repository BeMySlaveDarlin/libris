<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\models\Author;
use app\models\Book;
use app\models\SmsDelivery;
use app\models\Subscription;
use app\services\catalog\SubscriberNotifier;
use app\tests\Support\Fake\FailingSmsSender;
use app\tests\Support\Fake\RecordingSmsSender;
use Codeception\Test\Unit;
use Yii;

final class SubscriberNotifierTest extends Unit
{
    protected function _before(): void
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        foreach (['sms_delivery', 'subscription', 'book_author', 'book', 'author'] as $table) {
            Yii::$app->db->createCommand()->truncateTable("{{%{$table}}}")->execute();
        }
        Yii::$app->db->createCommand()->checkIntegrity(true)->execute();
    }

    public function testSendsToEverySubscriberOfEveryAuthor(): void
    {
        $first = $this->author('Аркадий');
        $second = $this->author('Борис');
        $book = $this->book('Совместная', [$first, $second]);
        $this->subscribe($first, '+79001111111');
        $this->subscribe($second, '+79002222222');

        $sender = $this->recordingSender();
        (new SubscriberNotifier($sender))->notifyAboutBook($book);

        $this->assertCount(2, $sender->sent);
        $this->assertSame(2, SmsDelivery::find()->where(['status' => SmsDelivery::STATUS_SENT])->count());
    }

    public function testDoesNotSendTwiceForSameBook(): void
    {
        $author = $this->author('Автор');
        $book = $this->book('Книга', [$author]);
        $this->subscribe($author, '+79001111111');

        $sender = $this->recordingSender();
        $notifier = new SubscriberNotifier($sender);
        $notifier->notifyAboutBook($book);
        $notifier->notifyAboutBook($book);

        $this->assertCount(1, $sender->sent);
        $this->assertSame(1, SmsDelivery::find()->count());
    }

    public function testStoresProviderErrorWithoutBreakingOtherSubscribers(): void
    {
        $author = $this->author('Автор');
        $book = $this->book('Книга', [$author]);
        $this->subscribe($author, '+79001111111');

        $sender = new FailingSmsSender('Неверный APIKEY');

        (new SubscriberNotifier($sender))->notifyAboutBook($book);

        $delivery = SmsDelivery::find()->one();
        $this->assertSame(SmsDelivery::STATUS_FAILED, $delivery->status);
        $this->assertSame('Неверный APIKEY', $delivery->error);
    }

    public function testIgnoresBookWithoutSubscribers(): void
    {
        $book = $this->book('Никому не нужная', [$this->author('Автор')]);

        $sender = $this->recordingSender();
        (new SubscriberNotifier($sender))->notifyAboutBook($book);

        $this->assertSame([], $sender->sent);
    }

    public function testMessageContainsTitleYearAndAuthors(): void
    {
        $author = $this->author('Станислав Лем');
        $book = $this->book('Солярис', [$author]);
        $this->subscribe($author, '+79001111111');

        $sender = $this->recordingSender();
        (new SubscriberNotifier($sender))->notifyAboutBook($book);

        $this->assertStringContainsString('Солярис', $sender->sent[0]['text']);
        $this->assertStringContainsString('Станислав Лем', $sender->sent[0]['text']);
    }

    private function recordingSender(): RecordingSmsSender
    {
        return new RecordingSmsSender();
    }

    private function author(string $name): Author
    {
        $author = new Author(['full_name' => $name]);
        $author->save();

        return $author;
    }

    /**
     * @param Author[] $authors
     */
    private function book(string $title, array $authors): Book
    {
        $book = new Book(['title' => $title, 'year' => 2024]);
        $book->save();
        foreach ($authors as $author) {
            $book->link('authors', $author);
        }

        return Book::find()->with('authors')->where(['id' => $book->id])->one();
    }

    private function subscribe(Author $author, string $phone): void
    {
        (new Subscription(['author_id' => $author->id, 'phone' => $phone]))->save();
    }
}
