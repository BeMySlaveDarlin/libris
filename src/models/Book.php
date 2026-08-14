<?php

declare(strict_types=1);

namespace app\models;

use app\models\behaviors\CarbonTimestampBehavior;
use app\models\query\BookQuery;
use app\validators\IsbnValidator;
use Carbon\CarbonImmutable;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $title
 * @property int $year
 * @property string|null $description
 * @property string|null $isbn
 * @property string|null $cover_path
 * @property string $created_at
 * @property string $updated_at
 * @property-read Author[] $authors
 * @property-read Genre[] $genres
 */
final class Book extends ActiveRecord
{
    public const YEAR_MIN = 1450;

    public static function tableName(): string
    {
        return '{{%book}}';
    }

    public static function find(): BookQuery
    {
        return new BookQuery(static::class);
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
            [['title', 'year'], 'required'],
            [['title', 'isbn'], 'trim'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['year'], 'integer', 'min' => self::YEAR_MIN, 'max' => CarbonImmutable::now()->year],
            [['isbn'], 'default', 'value' => null],
            [['isbn'], IsbnValidator::class],
            [['isbn'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'year' => 'Год выпуска',
            'description' => 'Описание',
            'isbn' => 'ISBN',
            'cover_path' => 'Обложка',
            'created_at' => 'Создана',
            'updated_at' => 'Обновлена',
        ];
    }

    public function getAuthors(): ActiveQuery
    {
        return $this->hasMany(Author::class, ['id' => 'author_id'])
            ->viaTable('{{%book_author}}', ['book_id' => 'id']);
    }

    public function getGenres(): ActiveQuery
    {
        return $this->hasMany(Genre::class, ['id' => 'genre_id'])
            ->viaTable('{{%book_genre}}', ['book_id' => 'id']);
    }

    /**
     * @return list<int>
     */
    public function genreIds(): array
    {
        return array_map(static fn(Genre $genre): int => $genre->id, $this->genres);
    }

    /**
     * @return list<int>
     */
    public function authorIds(): array
    {
        return array_map(static fn(Author $author): int => $author->id, $this->authors);
    }

    public function createdAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->created_at);
    }
}
