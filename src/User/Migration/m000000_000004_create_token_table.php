<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */


use Da\User\Helper\MigrationHelper;
use yii\db\Migration;

class m000000_000004_create_token_table extends Migration
{
    public function safeUp()
    {
        $this->createTable(
            '{{%token}}',
            [
                'userId' => $this->integer(),
                'code' => $this->string(32)->notNull(),
                'type' => $this->smallInteger(6)->notNull(),
                'createdAt' => $this->integer()->notNull(),
            ],
            MigrationHelper::resolveTableOptions($this->db->driverName)
        );

        $this->createIndex('idxTokenUserIdCodeType', '{{%token}}', ['userId', 'code', 'type'], true);

        $restrict = MigrationHelper::isMicrosoftSQLServer($this->db->driverName) ? 'NO ACTION' : 'RESTRICT';

        $this->addForeignKey('fkTokenUser', '{{%token}}', 'userId', '{{%user}}', 'id', 'CASCADE', $restrict);
    }

    public function safeDown()
    {
        $this->dropTable('{{%token}}');
    }
}
