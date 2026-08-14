<?php

declare(strict_types=1);

namespace app\migrations;

use yii\db\Migration;

final class M260814000900AddAuthorProfile extends Migration
{
    private const TABLE = '{{%author}}';

    public function safeUp(): void
    {
        $this->addColumn(self::TABLE, 'bio', $this->text()->__toString());
        $this->addColumn(self::TABLE, 'photo_path', $this->string(255)->__toString());
        $this->addColumn(self::TABLE, 'birth_date', $this->string(64)->__toString());
        $this->addColumn(self::TABLE, 'death_date', $this->string(64)->__toString());
    }

    public function safeDown(): void
    {
        $this->dropColumn(self::TABLE, 'death_date');
        $this->dropColumn(self::TABLE, 'birth_date');
        $this->dropColumn(self::TABLE, 'photo_path');
        $this->dropColumn(self::TABLE, 'bio');
    }
}
