<?php

declare(strict_types=1);

use app\models\Author;
use app\models\forms\AuthorSearch;
use app\models\Genre;
use app\services\storage\FileStorageInterface;
use app\rbac\Permissions;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;

/** @var yii\web\View $this */
/** @var AuthorSearch $search */
/** @var ActiveDataProvider $dataProvider */
/** @var Genre[] $genres */

$this->title = 'Авторы';
?>
<h1><?= Html::encode($this->title) ?></h1>

<form class="filters" method="get" action="<?= Html::encode(Url::to(['index'])) ?>">
    <div class="filter filter-wide">
        <label for="filter-q">Поиск</label>
        <?= Html::textInput('q', $search->q, ['id' => 'filter-q', 'placeholder' => 'часть имени']) ?>
    </div>
    <div class="filter filter-genres">
        <label>Пишет в жанрах</label>
        <div class="chips">
            <?php foreach ($genres as $genre): ?>
                <label class="chip">
                    <?= Html::checkbox('genres[]', in_array($genre->id, $search->genres, true), ['value' => $genre->id]) ?>
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
    <div class="summary">Найдено авторов: <?= $dataProvider->getTotalCount() ?></div>
</div>

<?php $storage = Yii::$container->get(FileStorageInterface::class); ?>
<?= ListView::widget([
    'dataProvider' => $dataProvider,
    'options' => ['class' => 'catalogue'],
    'itemOptions' => ['class' => 'card card-person'],
    'layout' => "<div class=\"cards\">{items}</div>\n{pager}",
    'emptyText' => 'По заданным условиям авторов не найдено.',
    'itemView' => static function (Author $model) use ($storage): string {
        $photo = $storage->url($model->photo_path);

        $portrait = $photo === null
            ? Html::tag('div', Html::tag('span', Html::encode(mb_substr($model->full_name, 0, 1))), ['class' => 'card-cover card-cover-empty card-portrait'])
            : Html::tag('div', Html::img($photo, ['alt' => Html::encode($model->full_name)]), ['class' => 'card-cover card-portrait']);

        $lifespan = $model->lifespan();
        $bio = $model->bio === null ? '' : Html::tag('div', Html::encode(mb_substr($model->bio, 0, 110)), ['class' => 'card-bio']);

        return Html::a(
            $portrait
            . Html::tag('div', Html::encode($model->full_name), ['class' => 'card-title'])
            . ($lifespan === null ? '' : Html::tag('div', Html::encode($lifespan), ['class' => 'card-meta']))
            . $bio,
            ['view', 'id' => $model->id],
            ['class' => 'card-link'],
        );
    },
]) ?>
