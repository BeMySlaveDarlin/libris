<?php

declare(strict_types=1);

namespace app\migrations;

use yii\db\Migration;

final class M260814000100CreateAuthorTable extends Migration
{
    private const TABLE = '{{%author}}';

    public function safeUp(): void
    {
        $this->createTable(self::TABLE, [
            'id' => $this->primaryKey(),
            'full_name' => $this->string(255)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $this->tableOptions());

        $this->createIndex('idx-author-full_name', self::TABLE, 'full_name');
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
