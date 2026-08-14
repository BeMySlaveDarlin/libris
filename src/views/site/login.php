<?php

declare(strict_types=1);

use app\models\forms\LoginForm;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var LoginForm $model */

$this->title = 'Вход';
?>
<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(); ?>
<?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
<?= $form->field($model, 'password')->passwordInput() ?>
<?= $form->field($model, 'rememberMe')->checkbox() ?>
<?= Html::submitButton('Войти', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>
