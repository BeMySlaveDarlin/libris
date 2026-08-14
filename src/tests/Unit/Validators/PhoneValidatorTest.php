<?php

declare(strict_types=1);

namespace app\tests\Unit\Validators;

use app\validators\PhoneValidator;
use Codeception\Test\Unit;
use yii\base\DynamicModel;

final class PhoneValidatorTest extends Unit
{
    public function testNormalisesRussianNumberToE164(): void
    {
        $model = $this->validate('8 (900) 123-45-67');

        $this->assertFalse($model->hasErrors('phone'));
        $this->assertSame('+79001234567', $model->getAttributes()['phone']);
    }

    public function testKeepsAlreadyNormalisedNumber(): void
    {
        $model = $this->validate('+79001234567');

        $this->assertFalse($model->hasErrors('phone'));
        $this->assertSame('+79001234567', $model->getAttributes()['phone']);
    }

    public function testRejectsGarbage(): void
    {
        $this->assertTrue($this->validate('не телефон')->hasErrors('phone'));
    }

    public function testRejectsTooShortNumber(): void
    {
        $this->assertTrue($this->validate('+7900')->hasErrors('phone'));
    }

    private function validate(string $phone): DynamicModel
    {
        $model = new DynamicModel(['phone' => $phone]);
        $model->addRule('phone', PhoneValidator::class);
        $model->validate();

        return $model;
    }
}
