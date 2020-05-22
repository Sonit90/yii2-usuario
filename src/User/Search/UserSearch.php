<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Da\User\Search;

use Da\User\Query\UserQuery;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class UserSearch extends Model
{
    /**
     * @var string
     */
    public $username;
    /**
     * @var string
     */
    public $email;
    /**
     * @var int
     */
    public $createAt;
    /**
     * @var int
     */
    public $lasLogiAt;
    /**
     * @var string
     */
    public $registratioIp;
    /**
     * @var string
     */
    public $lasLogiIp;
    /**
     * @var UserQuery
     */
    protected $query;

    /**
     * UserSearch constructor.
     *
     * @param UserQuery $query
     * @param array     $config
     */
    public function __construct(UserQuery $query, $config = [])
    {
        $this->query = $query;
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'safeFields' => [['username', 'email', 'registratioIp', 'createAt', 'lasLogiAt, lasLogiIp'], 'safe'],
            'createdDefault' => [['createAt', 'lasLogiAt'], 'default', 'value' => null],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('usuario', 'Username'),
            'email' => Yii::t('usuario', 'Email'),
            'createAt' => Yii::t('usuario', 'Registration time'),
            'registratioIp' => Yii::t('usuario', 'Registration IP'),
            'lasLogiAt' => Yii::t('usuario', 'Last login time'),
            'lasLogiIp' => Yii::t('usuario', 'Last login IP'),
        ];
    }

    /**
     * @param $params
     *
     * @throws InvalidParamException
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = $this->query;

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
            ]
        );

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if ($this->createAt !== null) {
            $date = strtotime($this->createAt);
            $query->andFilterWhere(['between', 'createAt', $date, $date + 3600 * 24]);
        }

        if ($this->lasLogiAt !== null) {
            $date = strtotime($this->lasLogiAt);
            $query->andFilterWhere(['between', 'lasLogiAt', $date, $date + 3600 * 24]);
        }

        $query
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['registratioIp' => $this->registratioIp])
            ->andFilterWhere(['lasLogiIp' => $this->lasLogiIp]);

        return $dataProvider;
    }
}
