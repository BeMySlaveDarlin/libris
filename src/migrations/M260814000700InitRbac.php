<?php

declare(strict_types=1);

namespace app\migrations;

use app\rbac\Permissions;
use app\rbac\Roles;
use yii\base\InvalidConfigException;
use yii\db\Migration;
use yii\rbac\DbManager;

final class M260814000700InitRbac extends Migration
{
    public function safeUp(): void
    {
        $auth = $this->authManager();

        $manageCatalog = $auth->createPermission(Permissions::MANAGE_CATALOG);
        $manageCatalog->description = 'Управление книгами и авторами';
        $auth->add($manageCatalog);

        $user = $auth->createRole(Roles::USER);
        $user->description = 'Аутентифицированный пользователь';
        $auth->add($user);
        $auth->addChild($user, $manageCatalog);
    }

    public function safeDown(): void
    {
        $auth = $this->authManager();

        $user = $auth->getRole(Roles::USER);
        if ($user !== null) {
            $auth->remove($user);
        }

        $manageCatalog = $auth->getPermission(Permissions::MANAGE_CATALOG);
        if ($manageCatalog !== null) {
            $auth->remove($manageCatalog);
        }
    }

    private function authManager(): DbManager
    {
        $auth = \Yii::$app->getAuthManager();
        if (!$auth instanceof DbManager) {
            throw new InvalidConfigException('Требуется authManager на основе yii\rbac\DbManager.');
        }

        return $auth;
    }
}
