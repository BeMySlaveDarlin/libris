<?php

declare(strict_types=1);

namespace app\models;

use Carbon\CarbonImmutable;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $author_id
 * @property string $phone
 * @property string $created_at
 * @property-read Author $author
 */
final class Subscription extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%subscription}}';
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
            [['author_id', 'phone'], 'required'],
            [['author_id'], 'integer'],
            [['author_id'], 'exist', 'targetClass' => Author::class, 'targetAttribute' => 'id'],
            [['phone'], 'string', 'max' => 16],
            [['phone'], 'unique', 'targetAttribute' => ['author_id', 'phone'],
                'message' => 'Этот номер уже подписан на автора.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'author_id' => 'Автор',
            'phone' => 'Телефон',
            'created_at' => 'Подписан',
        ];
    }

    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }
}
