<?php

declare(strict_types=1);

use app\models\Book;
use app\models\forms\BookSearch;
use app\models\Genre;
use app\services\storage\FileStorageInterface;
use app\rbac\Permissions;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;

/** @var yii\web\View $this */
/** @var BookSearch $search */
/** @var ActiveDataProvider $dataProvider */
/** @var Genre[] $genres */

$this->title = 'Каталог книг';
?>
<h1><?= Html::encode($this->title) ?></h1>

<form class="filters" method="get" action="<?= Html::encode(Url::to(['index'])) ?>">
    <div class="filter filter-wide">
        <label for="filter-q">Поиск</label>
        <?= Html::textInput('q', $search->q, [
            'id' => 'filter-q',
            'placeholder' => 'название, описание или автор',
        ]) ?>
    </div>
    <div class="filter filter-genres">
        <label>Жанры</label>
        <div class="chips">
            <?php foreach ($genres as $genre): ?>
                <label class="chip">
                    <?= Html::checkbox('genres[]', in_array($genre->id, $search->genres, true), [
                        'value' => $genre->id,
                    ]) ?>
                    <span><?= Html::encode($genre->name) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="filter">
        <?= Html::submitButton('Найти', ['class' => 'btn']) ?>
    </div>
    <?php if ($search->isFiltered()): ?>
        <div class="filter">
            <?= Html::a('Сбросить', ['index'], ['class' => 'btn btn-ghost']) ?>
        </div>
    <?php endif; ?>
</form>

<div class="list-head">
    <div class="summary">Найдено книг: <?= $dataProvider->getTotalCount() ?></div>
    <form class="sort-form" method="get" action="<?= Html::encode(Url::to(['index'])) ?>">
        <?php if ($search->q !== ''): ?>
            <?= Html::hiddenInput('q', $search->q) ?>
        <?php endif; ?>
        <?php foreach ($search->genres as $genreId): ?>
            <?= Html::hiddenInput('genres[]', (string) $genreId) ?>
        <?php endforeach; ?>
        <label for="sort">Сортировка</label>
        <?= Html::dropDownList('sort', $search->sort, BookSearch::SORTS, [
            'id' => 'sort',
            'onchange' => 'this.form.submit()',
        ]) ?>
        <noscript><?= Html::submitButton('Ок', ['class' => 'btn']) ?></noscript>
    </form>
</div>

<?php $storage = Yii::$container->get(FileStorageInterface::class); ?>
<?= ListView::widget([
    'dataProvider' => $dataProvider,
    'options' => ['class' => 'catalogue'],
    'itemOptions' => ['class' => 'card'],
    'layout' => "<div class=\"cards\">{items}</div>\n{pager}",
    'emptyText' => 'По заданным условиям книг не найдено.',
    'itemView' => static function (Book $model) use ($storage): string {
        $cover = $storage->url($model->cover_path);

        $figure = $cover === null
            ? Html::tag('div', Html::tag('span', Html::encode(mb_substr($model->title, 0, 1))), ['class' => 'card-cover card-cover-empty'])
            : Html::tag('div', Html::img($cover, ['alt' => Html::encode($model->title)]), ['class' => 'card-cover']);

        $authors = implode(', ', array_map(
            static fn($author): string => Html::encode($author->full_name),
            $model->authors,
        ));

        $genres = implode('', array_map(
            static fn($genre): string => Html::tag('span', Html::encode($genre->name), ['class' => 'tag']),
            array_slice($model->genres, 0, 2),
        ));

        return Html::a(
            $figure
            . Html::tag('div', Html::encode($model->title), ['class' => 'card-title'])
            . Html::tag('div', $model->year . ' · ' . $authors, ['class' => 'card-meta'])
            . ($genres === '' ? '' : Html::tag('div', $genres, ['class' => 'card-tags'])),
            ['view', 'id' => $model->id],
            ['class' => 'card-link'],
        );
    },
]) ?>
