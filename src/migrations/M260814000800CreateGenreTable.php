<?php

declare(strict_types=1);

namespace app\migrations;

use yii\db\Migration;

final class M260814000800CreateGenreTable extends Migration
{
    private const TABLE = '{{%genre}}';
    private const PIVOT = '{{%book_genre}}';

    public function safeUp(): void
    {
        $this->createTable(self::TABLE, [
            'id' => $this->primaryKey(),
            'name' => $this->string(64)->notNull(),
            'slug' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $this->tableOptions());

        $this->createIndex('uq-genre-slug', self::TABLE, 'slug', true);
        $this->createIndex('uq-genre-name', self::TABLE, 'name', true);

        $this->createTable(self::PIVOT, [
            'book_id' => $this->integer()->notNull(),
            'genre_id' => $this->integer()->notNull(),
        ], $this->tableOptions());

        $this->addPrimaryKey('pk-book_genre', self::PIVOT, ['book_id', 'genre_id']);
        $this->createIndex('idx-book_genre-genre_id', self::PIVOT, ['genre_id', 'book_id']);

        $this->addForeignKey('fk-book_genre-book_id', self::PIVOT, 'book_id', '{{%book}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-book_genre-genre_id', self::PIVOT, 'genre_id', self::TABLE, 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable(self::PIVOT);
        $this->dropTable(self::TABLE);
    }

    private function tableOptions(): ?string
    {
        return $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;
    }
}
