<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\Author;
use app\validators\PhoneValidator;
use yii\base\Model;

final class SubscriptionForm extends Model
{
    public ?int $authorId = null;
    public string $phone = '';

    public function rules(): array
    {
        return [
            [['authorId', 'phone'], 'required'],
            [['authorId'], 'integer'],
            [['authorId'], 'exist', 'targetClass' => Author::class, 'targetAttribute' => 'id'],
            [['phone'], 'trim'],
            [['phone'], PhoneValidator::class],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'authorId' => 'Автор',
            'phone' => 'Номер телефона',
        ];
    }
}
