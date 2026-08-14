<?php

declare(strict_types=1);

use app\models\Author;
use app\models\forms\BookForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var BookForm $form */
/** @var app\models\Genre[] $genres */

$authors = ArrayHelper::map(Author::find()->orderedByName()->all(), 'id', 'full_name');
$activeForm = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
?>
<?= $activeForm->field($form, 'title')->textInput(['maxlength' => 255]) ?>
<?= $activeForm->field($form, 'year')->input('number') ?>
<?= $activeForm->field($form, 'isbn')->textInput(['maxlength' => 17]) ?>
<?= $activeForm->field($form, 'authorIds')->listBox($authors, ['multiple' => true, 'size' => 8]) ?>
<div class="field">
    <label>Жанры</label>
    <div class="chips">
        <?php foreach ($genres as $genre): ?>
            <label class="chip">
                <?= Html::checkbox('BookForm[genreIds][]', in_array($genre->id, $form->genreIds, true), ['value' => $genre->id]) ?>
                <span><?= Html::encode($genre->name) ?></span>
            </label>
        <?php endforeach; ?>
    </div>
</div>
<?= $activeForm->field($form, 'newGenres')->textInput([
    'placeholder' => 'киберпанк, магический реализм',
])->hint('Через запятую. Новые жанры добавятся в справочник автоматически.') ?>
<?= $activeForm->field($form, 'description')->textarea(['rows' => 6]) ?>
<?= $activeForm->field($form, 'cover')->fileInput() ?>
<?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>
