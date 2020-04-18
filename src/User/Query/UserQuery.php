<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Da\User\Query;

use yii\db\ActiveQuery;

class UserQuery extends ActiveQuery
{

    /**
     * @param $email
     *
     * @return $this
     */
    public function whereEmail($email)
    {
        return $this->andWhere(['email' => $email]);
    }


    /**
     * @param $id
     *
     * @return $this
     */
    public function whereId($id)
    {
        return $this->andWhere(['id' => $id]);
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function whereNotId($id)
    {
        return $this->andWhere(['<>', 'id', $id]);
    }
}
