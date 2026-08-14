<?php

declare(strict_types=1);

use app\models\Author;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Author $author */

$this->title = 'Новый автор';
?>
<h1><?= Html::encode($this->title) ?></h1>
<?= $this->render('_form', ['author' => $author]) ?>
