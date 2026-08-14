<?php

declare(strict_types=1);

use app\models\forms\BookForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var BookForm $form */
/** @var app\models\Genre[] $genres */

$this->title = 'Новая книга';
?>
<h1><?= Html::encode($this->title) ?></h1>
<?= $this->render('_form', ['form' => $form, 'genres' => $genres]) ?>
