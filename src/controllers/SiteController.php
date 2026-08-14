<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\forms\LoginForm;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;

final class SiteController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['logout' => ['POST']],
            ],
        ];
    }

    public function actions(): array
    {
        return [
            'error' => ['class' => ErrorAction::class],
        ];
    }

    public function actionLogin(): Response|string
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $form = new LoginForm();
        if ($form->load(\Yii::$app->request->post()) && $form->login()) {
            return $this->goBack();
        }

        $form->password = '';

        return $this->render('login', ['model' => $form]);
    }

    public function actionLogout(): Response
    {
        \Yii::$app->user->logout();

        return $this->goHome();
    }
}
