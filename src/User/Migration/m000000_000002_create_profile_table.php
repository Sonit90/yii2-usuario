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

class m000000_000002_create_profile_table extends Migration
{
    public function safeUp()
    {
        $this->createTable(
            '{{%profile}}',
            [
                'userId' => $this->integer()->notNull(),
                'firstName' => $this->string(255),
                'lastName' => $this->string(255),
                'phone' => $this->string(15),
                'avatar' => $this->string(255),
                'publicEmail' => $this->string(255),
                'location' => $this->string(255),
                'timezone' => $this->string(40),
                'bio' => $this->text(),
            ],
            MigrationHelper::resolveTableOptions($this->db->driverName)
        );

        $this->addPrimaryKey('{{%profilePk}}', '{{%profile}}', 'userId');

        $restrict = MigrationHelper::isMicrosoftSQLServer($this->db->driverName) ? 'NO ACTION' : 'RESTRICT';

        $this->addForeignKey('fkProfileUser', '{{%profile}}', 'userId', '{{%user}}', 'id', 'CASCADE', $restrict);
    }

    public function safeDown()
    {
        $this->dropTable('{{%profile}}');
    }
}
