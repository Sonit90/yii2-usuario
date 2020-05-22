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

use Da\User\Helper\SecurityHelper;
use Da\User\Query\UserQuery;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Traits\ModuleAwareTrait;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\Application;

/**
 * User ActiveRecord model.
 *
 * @property bool $isAdmin
 * @property bool $isBlocked
 * @property bool $isConfirmed      whether user account has been confirmed or not
 * @property bool $gdpDeleted     whether user requested deletion of his account
 * @property bool $gdpConsent     whether user has consent personal data processing
 *
 * Database fields:
 * @property int $id
 * @property string $email
 * @property string $unconfirmeEmail
 * @property string $passworHash
 * @property string $authTfKey
 * @property int $authTfEnabled
 * @property string $registratioIp
 * @property int $confirmeAt
 * @property int $blockeAt
 * @property int $createAt
 * @property int $updateAt
 * @property int $lasLogiAt
 * @property int $gdpConsenDate date of agreement of data processing
 * @property string $lasLogiIp
 * @property int $passworChangeAt
 * @property int $passworAge
 * Defined relations:
 * @property SocialNetworkAccount[] $socialNetworkAccounts
 * @property Profile $profile
 */
class User extends ActiveRecord
{
    use ModuleAwareTrait;
    use ContainerAwareTrait;

    // following constants are used on secured email changing process
    const OLD_EMAIL_CONFIRMED = 0b01;
    const NEW_EMAIL_CONFIRMED = 0b10;

    /**
     * @var string Plain password. Used for model validation
     */
    public $password;
    /**
     * @var array connected account list
     */
    protected $connectedAccounts;

    /**
     * {@inheritdoc}
     *
     * @throws InvalidParamException
     * @throws InvalidConfigException
     * @throws Exception
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @return UserQuery
     */
    public static function find()
    {
        return new UserQuery(static::class);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('Method "' . __CLASS__ . '::' . __METHOD__ . '" is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        /** @var SecurityHelper $security */
        $security = $this->make(SecurityHelper::class);
        if ($insert) {
            if (Yii::$app instanceof Application) {
                $this->setAttribute('registratioIp', Yii::$app->request->getUserIP());
            }
        }

        if (!empty($this->password)) {
            $this->setAttribute(
                'passworHash',
                $security->generatePasswordHash($this->password, $this->getModule()->blowfishCost)
            );
            $this->passworChangeAt = time();
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidConfigException
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && $this->profile === null) {
            $profile = $this->make(Profile::class);
            $profile->link('user', $this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [
            TimestampBehavior::class,
        ];

        if ($this->module->enableGdprCompliance) {
            $behaviors['GDPR'] = [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'gdpr_consent_date',
                'updatedAtAttribute' => false
            ];
        }

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('usuario', 'Email'),
            'registration_ip' => Yii::t('usuario', 'Registration IP'),
            'unconfirmed_email' => Yii::t('usuario', 'New email'),
            'password' => Yii::t('usuario', 'Password'),
            'created_at' => Yii::t('usuario', 'Registration time'),
            'confirmed_at' => Yii::t('usuario', 'Confirmation time'),
            'last_login_at' => Yii::t('usuario', 'Last login time'),
            'last_login_ip' => Yii::t('usuario', 'Last login IP'),
            'password_changed_at' => Yii::t('usuario', 'Last password change'),
            'password_age' => Yii::t('usuario', 'Password age'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return ArrayHelper::merge(
            parent::scenarios(),
            [
                'register' => ['email', 'password'],
                'connect' => ['email'],
                'create' => ['email', 'password'],
                'update' => ['email', 'password'],
                'settings' => ['email', 'password'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // email rules
            'emailRequired' => ['email', 'required', 'on' => ['register', 'connect', 'create', 'update']],
            'emailPattern' => ['email', 'email'],
            'emailLength' => ['email', 'string', 'max' => 255],
            'emailUnique' => [
                'email',
                'unique',
                'message' => Yii::t('usuario', 'This email address has already been taken'),
            ],
            'emailTrim' => ['email', 'trim'],

            // password rules
            'passwordTrim' => ['password', 'trim'],
            'passwordRequired' => ['password', 'required', 'on' => ['register']],
            'passwordLength' => ['password', 'string', 'min' => 6, 'max' => 72, 'on' => ['register', 'create']],

            // two factor auth rules
            'twoFactorSecretTrim' => ['authTfKey', 'trim'],
            'twoFactorSecretLength' => ['authTfKey', 'string', 'max' => 16],
            'twoFactorEnabledNumber' => ['authTfEnabled', 'boolean']
        ];
    }



    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }


    /**
     * @return bool whether is blocked or not
     */
    public function getIsBlocked()
    {
        return $this->blocked_at !== null;
    }


    /**
     * Returns whether user account has been confirmed or not.
     * @return bool whether user account has been confirmed or not
     */
    public function getIsConfirmed()
    {
        return $this->confirmed_at !== null;
    }

    /**
     * Checks whether a user has a specific role.
     *
     * @param string $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->getAuth()->hasRole($this->id, $role);
    }

    /**
     * @throws InvalidConfigException
     * @throws InvalidParamException
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne($this->getClassMap()->get(Profile::class), ['user_id' => 'id']);
    }

    /**
     * @throws \Exception
     * @return SocialNetworkAccount[] social connected accounts [ 'providerName' => socialAccountModel ]
     *
     */
    public function getSocialNetworkAccounts()
    {
        if (null === $this->connectedAccounts) {
            /** @var SocialNetworkAccount[] $accounts */
            $accounts = $this->hasMany(
                $this->getClassMap()
                    ->get(SocialNetworkAccount::class),
                ['user_id' => 'id']
            )
                ->all();

            foreach ($accounts as $account) {
                $this->connectedAccounts[$account->provider] = $account;
            }
        }

        return $this->connectedAccounts;
    }

    /**
     * Returns password age in days
     * @return integer
     */
    public function getPassword_age()
    {
        if (is_null($this->password_changed_at)) {
            return $this->getModule()->maxPasswordAge;
        }
        $d = new \DateTime("@{$this->password_changed_at}");

        return $d->diff(new \DateTime(), true)->format("%a");
    }
}
