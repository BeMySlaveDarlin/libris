<?php

declare(strict_types=1);

namespace app\migrations;

use app\models\SmsDelivery;
use yii\db\Migration;

final class M260814000500CreateSmsDeliveryTable extends Migration
{
    private const TABLE = '{{%sms_delivery}}';

    public function safeUp(): void
    {
        $this->createTable(self::TABLE, [
            'id' => $this->primaryKey(),
            'subscription_id' => $this->integer()->notNull(),
            'book_id' => $this->integer()->notNull(),
            'status' => $this->string(16)->notNull()->defaultValue(SmsDelivery::STATUS_PENDING),
            'provider_message_id' => $this->string(64),
            'error' => $this->text(),
            'created_at' => $this->dateTime()->notNull(),
            'sent_at' => $this->dateTime(),
        ], $this->tableOptions());

        $this->createIndex('uq-sms_delivery-subscription_id-book_id', self::TABLE, ['subscription_id', 'book_id'], true);
        $this->createIndex('idx-sms_delivery-status', self::TABLE, 'status');

        $this->addForeignKey(
            'fk-sms_delivery-subscription_id',
            self::TABLE,
            'subscription_id',
            '{{%subscription}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
        $this->addForeignKey(
            'fk-sms_delivery-book_id',
            self::TABLE,
            'book_id',
            '{{%book}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
    }

    public function safeDown(): void
    {
        $this->dropTable(self::TABLE);
    }

    private function tableOptions(): ?string
    {
        return $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;
    }
}
