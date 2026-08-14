<?php

declare(strict_types=1);

namespace app\models\behaviors;

use Carbon\CarbonImmutable;
use yii\behaviors\TimestampBehavior;

final class CarbonTimestampBehavior extends TimestampBehavior
{
    public function init(): void
    {
        parent::init();

        $this->value ??= static fn(): string => CarbonImmutable::now()->toDateTimeString();
    }
}
