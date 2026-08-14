<?php

declare(strict_types=1);

namespace app\migrations;

use yii\db\Migration;

final class M260814000600CreateUserTable extends Migration
{
    private const TABLE = '{{%user}}';

    public function safeUp(): void
    {
        $this->createTable(self::TABLE, [
            'id' => $this->primaryKey(),
            'username' => $this->string(64)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $this->tableOptions());

        $this->createIndex('uq-user-username', self::TABLE, 'username', true);
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
