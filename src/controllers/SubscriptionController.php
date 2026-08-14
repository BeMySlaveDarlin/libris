<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\forms\SubscriptionForm;
use app\services\catalog\SubscriptionService;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

final class SubscriptionController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly SubscriptionService $subscriptions,
        array $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['subscribe' => ['POST']],
            ],
        ];
    }

    public function actionSubscribe(): Response
    {
        $form = new SubscriptionForm();

        if ($form->load(\Yii::$app->request->post()) && $form->validate()) {
            $this->subscriptions->subscribe($form);
            \Yii::$app->session->setFlash('success', 'Подписка оформлена.');
        } else {
            \Yii::$app->session->setFlash('error', implode(' ', $form->getErrorSummary(true)));
        }

        return $this->redirect(\Yii::$app->request->referrer ?? ['book/index']);
    }
}
