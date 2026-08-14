<?php

declare(strict_types=1);

use app\models\Book;
use app\rbac\Permissions;
use app\services\storage\FileStorageInterface;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Book $book */

$this->title = $book->title;
$coverUrl = Yii::$container->get(FileStorageInterface::class)->url($book->cover_path);
?>
<h1><?= Html::encode($book->title) ?></h1>

<div class="book">
    <?php if ($coverUrl !== null): ?>
        <div class="book-cover">
            <?= Html::img($coverUrl, ['alt' => Html::encode($book->title), 'class' => 'cover']) ?>
        </div>
    <?php endif; ?>

    <div class="book-body">
        <?php if ($book->genres !== []): ?>
            <p class="entry-tags">
                <?php foreach ($book->genres as $genre): ?>
                    <?= Html::a(Html::encode($genre->name), ['index', 'genres' => [$genre->id]], ['class' => 'tag']) ?>
                <?php endforeach; ?>
            </p>
        <?php endif; ?>

        <dl>
            <dt>Год выпуска</dt>
            <dd><?= Html::encode((string) $book->year) ?></dd>
            <dt>ISBN</dt>
            <dd><?= Html::encode($book->isbn ?? '—') ?></dd>
            <dt>Авторы</dt>
            <dd>
                <?php foreach ($book->authors as $index => $author): ?>
                    <?= $index > 0 ? ', ' : '' ?><?= Html::a(Html::encode($author->full_name), ['author/view', 'id' => $author->id]) ?>
                <?php endforeach; ?>
            </dd>
        </dl>

        <p class="hint">Подписка на новинки автора оформляется на его странице.</p>

        <h2>Описание</h2>
        <p><?= nl2br(Html::encode($book->description ?? 'Описание не заполнено.')) ?></p>
    </div>
</div>

<?php if (Yii::$app->user->can(Permissions::MANAGE_CATALOG)): ?>
    <p>
        <?= Html::a('Редактировать', ['update', 'id' => $book->id], ['class' => 'btn btn-ghost']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $book->id], [
            'class' => 'btn btn-danger',
            'data' => ['method' => 'post', 'confirm' => 'Удалить книгу?'],
        ]) ?>
    </p>
<?php endif; ?>
