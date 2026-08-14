<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

final class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/libris.css',
    ];
    public $depends = [
        \yii\web\YiiAsset::class,
    ];
}
