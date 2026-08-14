<?php

declare(strict_types=1);

use app\models\Book;
use app\models\forms\BookForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var BookForm $form */
/** @var app\models\Genre[] $genres */
/** @var Book $book */

$this->title = 'Редактирование: ' . $book->title;
?>
<h1><?= Html::encode($this->title) ?></h1>
<?= $this->render('_form', ['form' => $form, 'genres' => $genres]) ?>
