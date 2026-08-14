<?php

declare(strict_types=1);

namespace app\services\catalog;

use app\models\forms\SubscriptionForm;
use app\models\Subscription;
use RuntimeException;

final readonly class SubscriptionService
{
    public function subscribe(SubscriptionForm $form): Subscription
    {
        $existing = Subscription::findOne(['author_id' => $form->authorId, 'phone' => $form->phone]);
        if ($existing !== null) {
            return $existing;
        }

        $subscription = new Subscription();
        $subscription->author_id = (int) $form->authorId;
        $subscription->phone = $form->phone;

        if (!$subscription->save()) {
            throw new RuntimeException('Не удалось оформить подписку.');
        }

        return $subscription;
    }
}
