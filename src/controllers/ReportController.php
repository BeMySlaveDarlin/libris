<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\forms\ReportFilter;
use app\services\catalog\CatalogService;
use app\services\catalog\ReportService;
use Carbon\CarbonImmutable;
use yii\web\Controller;

final class ReportController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly ReportService $reports,
        private readonly CatalogService $catalogue,
        array $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): string
    {
        $filter = new ReportFilter();
        $filter->load(\Yii::$app->request->queryParams);
        $filter->normalise($this->reports->latestYear() ?? CarbonImmutable::now()->year);

        return $this->render('index', [
            'filter' => $filter,
            'years' => $this->reports->availableYears(),
            'rows' => $this->reports->topAuthors($filter),
            'genres' => $this->catalogue->genres(),
        ]);
    }
}
