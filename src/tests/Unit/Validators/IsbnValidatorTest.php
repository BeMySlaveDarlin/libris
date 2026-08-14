<?php

declare(strict_types=1);

namespace app\tests\Unit\Validators;

use app\validators\IsbnValidator;
use Codeception\Test\Unit;
use yii\base\DynamicModel;

final class IsbnValidatorTest extends Unit
{
    public function testConvertsIsbn10ToIsbn13(): void
    {
        $model = $this->validate('0-441-47812-3');

        $this->assertFalse($model->hasErrors('isbn'));
        $this->assertSame('9780441478125', $model->getAttributes()['isbn']);
    }

    public function testAcceptsIsbn13WithHyphens(): void
    {
        $model = $this->validate('978-0-441-47812-5');

        $this->assertFalse($model->hasErrors('isbn'));
        $this->assertSame('9780441478125', $model->getAttributes()['isbn']);
    }

    public function testRejectsWrongCheckDigit(): void
    {
        $this->assertTrue($this->validate('978-0-441-47812-4')->hasErrors('isbn'));
    }

    public function testSkipsEmptyValue(): void
    {
        $this->assertFalse($this->validate('')->hasErrors('isbn'));
    }

    private function validate(string $isbn): DynamicModel
    {
        $model = new DynamicModel(['isbn' => $isbn]);
        $model->addRule('isbn', IsbnValidator::class);
        $model->validate();

        return $model;
    }
}
