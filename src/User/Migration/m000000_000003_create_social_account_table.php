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

class m000000_000003_create_social_account_table extends Migration
{
    public function safeUp()
    {
        $this->createTable(
            '{{%socialAccount}}',
            [
                'id' => $this->primaryKey(),
                'userId' => $this->integer(),
                'provider' => $this->string(255)->notNull(),
                'clientId' => $this->string(255)->notNull(),
                'code' => $this->string(32),
                'email' => $this->string(255),
                'username' => $this->string(255),
                'data' => $this->text(),
                'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ],
            MigrationHelper::resolveTableOptions($this->db->driverName)
        );

        $this->createIndex(
            'idxSocialAccountProviderClient_id',
            '{{%socialAccount}}',
            ['provider', 'clientId'],
            true
        );

        $this->createIndex('idxSocialAccountCode', '{{%socialAccount}}', 'code', true);

        $this->addForeignKey(
            'fkSocialAccountUser',
            '{{%socialAccount}}',
            'userId',
            '{{%user}}',
            'id',
            'CASCADE',
            (MigrationHelper::isMicrosoftSQLServer($this->db->driverName) ? 'NO ACTION' : 'RESTRICT')
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%socialAccount}}');
    }
}
