<?php

declare(strict_types=1);

namespace app\models;

use app\models\behaviors\CarbonTimestampBehavior;
use app\models\query\AuthorQuery;
use Carbon\CarbonImmutable;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $full_name
 * @property string|null $bio
 * @property string|null $photo_path
 * @property string|null $birth_date
 * @property string|null $death_date
 * @property string $created_at
 * @property string $updated_at
 * @property-read Book[] $books
 * @property-read Subscription[] $subscriptions
 */
final class Author extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%author}}';
    }

    public static function find(): AuthorQuery
    {
        return new AuthorQuery(static::class);
    }

    public function behaviors(): array
    {
        return [
            CarbonTimestampBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['full_name'], 'required'],
            [['full_name'], 'trim'],
            [['full_name'], 'string', 'min' => 3, 'max' => 255],
            [['bio'], 'string'],
            [['photo_path'], 'string', 'max' => 255],
            [['birth_date', 'death_date'], 'string', 'max' => 64],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'full_name' => 'ФИО',
            'bio' => 'О писателе',
            'photo_path' => 'Фотография',
            'birth_date' => 'Родился',
            'death_date' => 'Умер',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлён',
        ];
    }

    public function getBooks(): ActiveQuery
    {
        return $this->hasMany(Book::class, ['id' => 'book_id'])
            ->viaTable('{{%book_author}}', ['author_id' => 'id']);
    }

    public function getSubscriptions(): ActiveQuery
    {
        return $this->hasMany(Subscription::class, ['author_id' => 'id']);
    }

    public function lifespan(): ?string
    {
        if ($this->birth_date === null && $this->death_date === null) {
            return null;
        }

        return trim(($this->birth_date ?? '?') . ' — ' . ($this->death_date ?? 'наши дни'));
    }

    public function createdAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->created_at);
    }
}
