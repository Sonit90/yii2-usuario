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

class m000000_000005_create_sessions_table extends Migration
{
    public function safeUp()
    {
        $this->createTable(
            '{{%sessions}}',
            [
                'id' => $this->primaryKey(),
                'userId' => $this->integer(),
                'ua' => $this->string(200)->notNull(),
                'refreshToken' => $this->string(200),
                'fingerprint' => $this->string(200)->notNull(),
                'createdAt' => $this->integer(),
                'expiresIn' => $this->integer(),
            ],
            MigrationHelper::resolveTableOptions($this->db->driverName)
        );

        $this->createIndex(
            'idxSessionsUserIdCodeType',
            '{{%sessions}}',
            ['id','userId', 'refreshToken', 'fingerprint'],
            true
        );

        $restrict = MigrationHelper::isMicrosoftSQLServer($this->db->driverName) ? 'NO ACTION' : 'RESTRICT';

        $this->addForeignKey('fkSessionsUser', '{{%sessions}}', 'userId', '{{%user}}', 'id', 'CASCADE', $restrict);
    }

    public function safeDown()
    {
        $this->dropTable('{{%sessions}}');
    }
}
