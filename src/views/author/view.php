<?php

declare(strict_types=1);

use app\models\Author;
use app\models\Book;
use app\models\forms\SubscriptionForm;
use app\rbac\Permissions;
use app\services\storage\FileStorageInterface;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var Author $author */
/** @var Book[] $books */

$this->title = $author->full_name;
$subscription = new SubscriptionForm(['authorId' => $author->id]);
?>
<h1><?= Html::encode($author->full_name) ?></h1>

<?php $photo = Yii::$container->get(FileStorageInterface::class)->url($author->photo_path); ?>
<div class="person">
    <?php if ($photo !== null): ?>
        <div class="person-photo"><?= Html::img($photo, ['alt' => Html::encode($author->full_name), 'class' => 'cover']) ?></div>
    <?php endif; ?>
    <div class="person-body">
        <?php if ($author->lifespan() !== null): ?>
            <p class="entry-meta"><?= Html::encode($author->lifespan()) ?></p>
        <?php endif; ?>
        <?php if ($author->bio !== null): ?>
            <p><?= Html::encode($author->bio) ?></p>
        <?php endif; ?>
    </div>
</div>

<?php if (Yii::$app->user->can(Permissions::MANAGE_CATALOG)): ?>
    <p>
        <?= Html::a('Редактировать', ['update', 'id' => $author->id], ['class' => 'btn btn-secondary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $author->id], [
            'class' => 'btn btn-danger',
            'data' => ['method' => 'post', 'confirm' => 'Удалить автора вместе со связями?'],
        ]) ?>
    </p>
<?php endif; ?>

<h2>Книги</h2>
<?php foreach ($books as $book): ?>
    <div class="entry">
        <div class="entry-title"><?= Html::a(Html::encode($book->title), ['book/view', 'id' => $book->id]) ?></div>
        <div class="entry-meta"><?= (int) $book->year ?><?= $book->isbn === null ? '' : ' · ISBN ' . Html::encode($book->isbn) ?></div>
        <?php if ($book->genres !== []): ?>
            <div class="entry-tags">
                <?php foreach ($book->genres as $genre): ?>
                    <span class="tag"><?= Html::encode($genre->name) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<h2>Подписаться на новинки</h2>
<?php $form = ActiveForm::begin(['action' => ['subscription/subscribe']]); ?>
<?= Html::activeHiddenInput($subscription, 'authorId') ?>
<?= $form->field($subscription, 'phone')->textInput(['placeholder' => '+7 900 000-00-00']) ?>
<?= Html::submitButton('Подписаться', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>
