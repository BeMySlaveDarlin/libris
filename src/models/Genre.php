<?php

declare(strict_types=1);

namespace app\models;

use app\models\behaviors\CarbonTimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $created_at
 * @property string $updated_at
 * @property-read Book[] $books
 */
final class Genre extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%genre}}';
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
            [['name'], 'required'],
            [['name'], 'trim'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
            [['slug'], 'default', 'value' => fn(): string => Inflector::slug($this->name)],
            [['slug'], 'string', 'max' => 64],
            [['slug'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'Жанр',
            'slug' => 'Идентификатор',
        ];
    }

    public function getBooks(): ActiveQuery
    {
        return $this->hasMany(Book::class, ['id' => 'book_id'])
            ->viaTable('{{%book_genre}}', ['genre_id' => 'id']);
    }
}
