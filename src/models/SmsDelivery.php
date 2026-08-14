<?php

declare(strict_types=1);

namespace app\models;

use Carbon\CarbonImmutable;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $subscription_id
 * @property int $book_id
 * @property string $status
 * @property string|null $provider_message_id
 * @property string|null $error
 * @property string $created_at
 * @property string|null $sent_at
 * @property-read Subscription $subscription
 * @property-read Book $book
 */
final class SmsDelivery extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    public static function tableName(): string
    {
        return '{{%sms_delivery}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'updatedAtAttribute' => false,
                'value' => static fn(): string => CarbonImmutable::now()->toDateTimeString(),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['subscription_id', 'book_id'], 'required'],
            [['subscription_id', 'book_id'], 'integer'],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_SENT, self::STATUS_FAILED]],
            [['provider_message_id'], 'string', 'max' => 64],
            [['error'], 'string'],
        ];
    }

    public function markSent(?string $providerMessageId): void
    {
        $this->status = self::STATUS_SENT;
        $this->provider_message_id = $providerMessageId;
        $this->sent_at = CarbonImmutable::now()->toDateTimeString();
        $this->error = null;
    }

    public function markFailed(string $error): void
    {
        $this->status = self::STATUS_FAILED;
        $this->error = $error;
    }

    public function getSubscription(): ActiveQuery
    {
        return $this->hasOne(Subscription::class, ['id' => 'subscription_id']);
    }

    public function getBook(): ActiveQuery
    {
        return $this->hasOne(Book::class, ['id' => 'book_id']);
    }
}
