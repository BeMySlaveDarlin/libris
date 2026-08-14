<?php

declare(strict_types=1);

namespace app\migrations;

use yii\db\Migration;

final class M260814000200CreateBookTable extends Migration
{
    private const TABLE = '{{%book}}';

    public function safeUp(): void
    {
        $this->createTable(self::TABLE, [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'year' => $this->smallInteger()->unsigned()->notNull(),
            'description' => $this->text(),
            'isbn' => $this->string(17),
            'cover_path' => $this->string(255),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $this->tableOptions());

        $this->createIndex('idx-book-year', self::TABLE, 'year');
        $this->createIndex('idx-book-title', self::TABLE, 'title');
        $this->createIndex('uq-book-isbn', self::TABLE, 'isbn', true);
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
