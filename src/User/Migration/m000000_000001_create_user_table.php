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

class m000000_000001_create_user_table extends Migration
{
    public function safeUp()
    {
        $this->createTable(
            '{{%user}}',
            [
                'id' => $this->primaryKey(),
                'email' => $this->string(255)->notNull(),
                'passwordHash' => $this->string(60)->notNull(),
                'passwordChangedAt' => $this->integer()->null(),
                'unconfirmedEmail' => $this->string(255),
                'registrationIp' => $this->string(45),
                'confirmedAt' => $this->integer()->null(),
                'blockedAt' => $this->integer()->null(),
                'updatedAt' => $this->integer(),
                'createdAt' => $this->integer(),
                'deletedAt' => $this->integer()->null(),
                'lastLoginAt' => $this->integer()->null(),
                'lastLoginIp' => $this->string(45)->null(),
                'authTfKey' => $this->string(16),
                'authTfEnabled' => $this->boolean()->defaultValue(false),

            ],
            MigrationHelper::resolveTableOptions($this->db->driverName)
        );

        $this->createIndex('idxUserEmail', '{{%user}}', 'email', true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
