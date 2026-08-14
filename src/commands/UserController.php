<?php

declare(strict_types=1);

namespace app\commands;

use app\models\User;
use app\rbac\Roles;
use yii\console\Controller;
use yii\console\ExitCode;

final class UserController extends Controller
{
    public function actionCreate(string $username, string $password): int
    {
        if (User::findByUsername($username) !== null) {
            $this->stderr("Пользователь {$username} уже существует." . PHP_EOL);

            return ExitCode::DATAERR;
        }

        $user = new User(['username' => $username]);
        $user->setPassword($password);
        $user->generateAuthKey();

        if (!$user->save()) {
            $this->stderr(implode(PHP_EOL, $user->getErrorSummary(true)) . PHP_EOL);

            return ExitCode::DATAERR;
        }

        $auth = \Yii::$app->getAuthManager();
        $auth->assign($auth->getRole(Roles::USER), $user->id);

        $this->stdout("Пользователь {$username} создан." . PHP_EOL);

        return ExitCode::OK;
    }
}
