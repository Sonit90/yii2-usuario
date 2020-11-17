<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Da\User\Model;

use Da\User\Traits\AuthManagerAwareTrait;
use Da\User\Validator\RbacItemsValidator;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;

class Assignment extends Model
{
    use AuthManagerAwareTrait;

    public $items = [];
    public $userId;
    public $updated = false;

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if ($this->userId === null) {
            throw new InvalidConfigException('"userId" must be set.');
        }

        $this->items = array_keys($this->getAuthManager()->getItemsByUser($this->userId));
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'items' => Yii::t('usuario', 'Items'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['userId', 'required'],
            ['items', RbacItemsValidator::class],
            ['userId', 'integer'],
        ];
    }
}
