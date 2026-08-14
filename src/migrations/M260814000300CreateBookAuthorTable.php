<?php

declare(strict_types=1);

namespace app\migrations;

use yii\db\Migration;

final class M260814000300CreateBookAuthorTable extends Migration
{
    private const TABLE = '{{%book_author}}';

    public function safeUp(): void
    {
        $this->createTable(self::TABLE, [
            'book_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
        ], $this->tableOptions());

        $this->addPrimaryKey('pk-book_author', self::TABLE, ['book_id', 'author_id']);
        $this->createIndex('idx-book_author-author_id', self::TABLE, ['author_id', 'book_id']);

        $this->addForeignKey(
            'fk-book_author-book_id',
            self::TABLE,
            'book_id',
            '{{%book}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
        $this->addForeignKey(
            'fk-book_author-author_id',
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
