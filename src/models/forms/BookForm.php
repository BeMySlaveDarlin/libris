<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\Author;
use app\models\Book;
use app\models\Genre;
use app\validators\IsbnValidator;
use Carbon\CarbonImmutable;
use yii\base\Model;
use yii\web\UploadedFile;

final class BookForm extends Model
{
    public string $title = '';
    public ?int $year = null;
    public ?string $description = null;
    public ?string $isbn = null;
    /** @var list<int> */
    public array $authorIds = [];
    /** @var list<int> */
    public array $genreIds = [];
    public string $newGenres = '';
    public ?UploadedFile $cover = null;

    public static function fromBook(Book $book): self
    {
        $form = new self();
        $form->title = $book->title;
        $form->year = $book->year;
        $form->description = $book->description;
        $form->isbn = $book->isbn;
        $form->authorIds = $book->authorIds();
        $form->genreIds = $book->genreIds();

        return $form;
    }

    public function rules(): array
    {
        return [
            [['title', 'year', 'authorIds'], 'required'],
            [['title', 'isbn'], 'trim'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['year'], 'integer', 'min' => Book::YEAR_MIN, 'max' => CarbonImmutable::now()->year],
            [['isbn'], IsbnValidator::class],
            [['authorIds'], 'each', 'rule' => ['integer']],
            [['authorIds'], 'each', 'rule' => ['exist', 'targetClass' => Author::class, 'targetAttribute' => 'id']],
            [['genreIds'], 'each', 'rule' => ['integer']],
            [['genreIds'], 'each', 'rule' => ['exist', 'targetClass' => Genre::class, 'targetAttribute' => 'id']],
            [['newGenres'], 'trim'],
            [['newGenres'], 'string', 'max' => 255],
            [['cover'], 'file',
                'extensions' => \Yii::$app->params['coverExtensions'],
                'maxSize' => \Yii::$app->params['coverMaxSize'],
                'checkExtensionByMimeType' => true,
                'skipOnEmpty' => true,
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title' => 'Название',
            'year' => 'Год выпуска',
            'description' => 'Описание',
            'isbn' => 'ISBN',
            'authorIds' => 'Авторы',
            'genreIds' => 'Жанры',
            'newGenres' => 'Новые жанры',
            'cover' => 'Обложка',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    /**
     * @return list<string>
     */
    public function newGenreNames(): array
    {
        if ($this->newGenres === '') {
            return [];
        }

        $names = array_map('trim', explode(',', $this->newGenres));

        return array_values(array_unique(array_filter($names, static fn(string $name): bool => $name !== '')));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function load($data, $formName = null): bool
    {
        $scope = $formName ?? $this->formName();
        if ($scope !== '' && isset($data[$scope])) {
            unset($data[$scope]['cover']);
        }

        $loaded = parent::load($data, $formName);
        $this->cover = UploadedFile::getInstance($this, 'cover');

        return $loaded;
    }
}
