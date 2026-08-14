<?php

declare(strict_types=1);

namespace app\models;

use app\models\behaviors\CarbonTimestampBehavior;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $username
 * @property string $password_hash
 * @property string $auth_key
 * @property string $created_at
 * @property string $updated_at
 */
final class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public function behaviors(): array
    {
        return [
            CarbonTimestampBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['username', 'password_hash', 'auth_key'], 'required'],
            [['username'], 'string', 'max' => 64],
            [['username'], 'unique'],
        ];
    }

    public static function findIdentity($id): ?self
    {
        return static::findOne(['id' => $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null): never
    {
        throw new NotSupportedException('Аутентификация по токену не поддерживается.');
    }

    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username]);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return hash_equals($this->auth_key, (string) $authKey);
    }

    public function validatePassword(string $password): bool
    {
        return \Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = \Yii::$app->getSecurity()->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = \Yii::$app->getSecurity()->generateRandomString();
    }
}
