<?php

declare(strict_types=1);

use app\assets\AppAsset;
use app\rbac\Permissions;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $content */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title ?? 'Libris') ?></title>
    <?= Html::csrfMetaTags() ?>
    <link rel="icon" type="image/svg+xml" href="<?= Yii::getAlias('@web') ?>/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="page">
    <header class="masthead">
        <h1><?= Html::a('Libris<span class="dot">.</span>', ['/book/index']) ?></h1>
        <div class="subtitle">Каталог книг и подписки на новинки авторов</div>
    </header>

    <div class="dateline">
        <nav>
            <?= Html::a('Книги', ['/book/index']) ?>
            <?= Html::a('Авторы', ['/author/index']) ?>
            <?= Html::a('Отчёт', ['/report/index']) ?>
            <?php if (Yii::$app->user->can(Permissions::MANAGE_CATALOG)): ?>
                <span class="nav-actions">
                    <?= Html::a('+ книга', ['/book/create'], ['class' => 'btn btn-mini']) ?>
                    <?= Html::a('+ автор', ['/author/create'], ['class' => 'btn btn-mini']) ?>
                </span>
            <?php endif; ?>
        </nav>
        <div>
            <?php if (Yii::$app->user->isGuest): ?>
                <?= Html::a('Вход', ['/site/login']) ?>
            <?php else: ?>
                <?= Html::beginForm(['/site/logout']) ?>
                <?= Html::encode(Yii::$app->user->identity->username) ?>
                <?= Html::submitButton('Выход', ['class' => 'btn btn-ghost']) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>
        </div>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="flash <?= $type === 'error' ? 'flash-error' : '' ?>"><?= Html::encode((string) $message) ?></div>
    <?php endforeach; ?>

    <main>
        <?= $content ?>
    </main>

    <footer class="footer">
        <span>Libris</span>
        <span><?= Html::a('bemyslavedarlin', 'https://github.com/BeMySlaveDarlin') ?></span>
    </footer>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
