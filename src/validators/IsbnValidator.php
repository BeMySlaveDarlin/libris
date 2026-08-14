<?php

declare(strict_types=1);

namespace app\validators;

use Nicebooks\Isbn\Exception\IsbnException;
use Nicebooks\Isbn\Isbn;
use yii\validators\Validator;

final class IsbnValidator extends Validator
{
    public $skipOnEmpty = true;

    public function init(): void
    {
        parent::init();

        $this->message ??= 'Значение «{attribute}» не является корректным ISBN.';
    }

    public function validateAttribute($model, $attribute): void
    {
        try {
            $isbn = Isbn::of((string) $model->{$attribute})->to13();
        } catch (IsbnException) {
            $this->addError($model, $attribute, $this->message);

            return;
        }

        $model->{$attribute} = $isbn->toString();
    }
}
