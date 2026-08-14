<?php

declare(strict_types=1);

use app\models\Author;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var Author $author */

$form = ActiveForm::begin();
?>
<?= $form->field($author, 'full_name')->textInput(['maxlength' => 255]) ?>
<?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>
