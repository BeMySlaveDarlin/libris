<?php

declare(strict_types=1);

namespace app\validators;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use yii\validators\Validator;

final class PhoneValidator extends Validator
{
    public string $defaultRegion = 'RU';

    public function init(): void
    {
        parent::init();

        $this->message ??= 'Значение «{attribute}» не является корректным номером телефона.';
    }

    public function validateAttribute($model, $attribute): void
    {
        $util = PhoneNumberUtil::getInstance();

        try {
            $number = $util->parse((string) $model->{$attribute}, $this->defaultRegion);
        } catch (NumberParseException) {
            $this->addError($model, $attribute, $this->message);

            return;
        }

        if (!$util->isValidNumber($number)) {
            $this->addError($model, $attribute, $this->message);

            return;
        }

        $model->{$attribute} = $util->format($number, PhoneNumberFormat::E164);
    }
}
