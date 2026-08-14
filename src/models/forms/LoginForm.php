<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\User;
use yii\base\Model;

final class LoginForm extends Model
{
    public string $username = '';
    public string $password = '';
    public bool $rememberMe = true;

    private ?User $_user = null;
    private bool $_userLoaded = false;

    public function rules(): array
    {
        return [
            [['username', 'password'], 'required'],
            [['rememberMe'], 'boolean'],
            [['password'], 'validatePassword'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username' => 'Логин',
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить меня',
        ];
    }

    public function validatePassword(string $attribute): void
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->findUser();
        if ($user === null || !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Неверный логин или пароль.');
        }
    }

    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->findUser();
        if ($user === null) {
            return false;
        }

        return \Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
    }

    private function findUser(): ?User
    {
        if (!$this->_userLoaded) {
            $this->_user = User::findByUsername($this->username);
            $this->_userLoaded = true;
        }

        return $this->_user;
    }
}
