<?php

declare(strict_types=1);

namespace app\migrations;

use yii\db\Migration;

final class M260814000400CreateSubscriptionTable extends Migration
{
    private const TABLE = '{{%subscription}}';

    public function safeUp(): void
    {
        $this->createTable(self::TABLE, [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'phone' => $this->string(16)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $this->tableOptions());

        $this->createIndex('uq-subscription-author_id-phone', self::TABLE, ['author_id', 'phone'], true);

        $this->addForeignKey(
            'fk-subscription-author_id',
            self::TABLE,
            'author_id',
            '{{%author}}',
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
