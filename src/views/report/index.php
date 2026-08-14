<?php

declare(strict_types=1);

use app\models\forms\ReportFilter;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var ReportFilter $filter */
/** @var int[] $years */
/** @var array<int, array{author_id: int, full_name: string, books_count: int}> $rows */
/** @var app\models\Genre[] $genres */

$this->title = 'ТОП авторов за ' . $filter->year . ' год';
?>
<h1><?= Html::encode($this->title) ?></h1>

<form class="filters" method="get" action="<?= Html::encode(Url::to(['index'])) ?>">
    <div class="filter">
        <label for="filter-year">Год</label>
        <?= Html::dropDownList('year', $filter->year, array_combine($years, $years), ['id' => 'filter-year']) ?>
    </div>
    <div class="filter">
        <label for="filter-limit">Показать</label>
        <?= Html::dropDownList('limit', $filter->limit, array_combine(ReportFilter::LIMITS, ReportFilter::LIMITS), ['id' => 'filter-limit']) ?>
    </div>
    <div class="filter filter-wide">
        <label for="filter-author">Автор</label>
        <?= Html::textInput('author', $filter->author, ['id' => 'filter-author', 'placeholder' => 'часть имени']) ?>
    </div>
    <div class="filter">
        <label for="filter-min">Книг не менее</label>
        <?= Html::input('number', 'minBooks', $filter->minBooks, ['id' => 'filter-min', 'min' => 1, 'placeholder' => '1']) ?>
    </div>
    <div class="filter filter-genres">
        <label>Жанры</label>
        <div class="chips">
            <?php foreach ($genres as $genre): ?>
                <label class="chip">
                    <?= Html::checkbox('genres[]', in_array($genre->id, $filter->genres, true), ['value' => $genre->id]) ?>
                    <span><?= Html::encode($genre->name) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="filter">
        <?= Html::submitButton('Показать', ['class' => 'btn']) ?>
    </div>
    <?php if ($filter->isFiltered()): ?>
        <div class="filter">
            <?= Html::a('Сбросить', ['index', 'year' => $filter->year], ['class' => 'btn btn-ghost']) ?>
        </div>
    <?php endif; ?>
</form>

<?php if ($rows === []): ?>
    <p>По заданным условиям книг не найдено.</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Автор</th>
            <th>Книг за год</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $index => $row): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= Html::a(Html::encode($row['full_name']), ['author/view', 'id' => $row['author_id']]) ?></td>
                <td><?= Html::encode((string) $row['books_count']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
