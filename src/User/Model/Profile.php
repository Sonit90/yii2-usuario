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

use Da\User\Query\ProfileQuery;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Traits\ModuleAwareTrait;
use Da\User\Validator\TimeZoneValidator;
use DateTime;
use DateTimeZone;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $user_id
 * @property string $name
 * @property string $public_email
 * @property string $location
 * @property string $website
 * @property string $bio
 * @property string $timezone
 * @property User $user
 */
class Profile extends ActiveRecord
{
    use ModuleAwareTrait;
    use ContainerAwareTrait;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%profile}}';
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     */
    public function rules()
    {
        return [
            'bioString' => ['bio', 'string'],
            'timeZoneValidation' => [
                'timezone',
                function ($attribute) {
                    if ($this->make(TimeZoneValidator::class, [$this->{$attribute}])->validate() === false) {
                        $this->addError($attribute, Yii::t('usuario', 'Time zone is not valid'));
                    }
                },
            ],
            'publicEmailPattern' => ['public_email', 'email'],
            'websiteUrl' => ['website', 'url'],
            'nameLength' => ['name', 'string', 'max' => 255],
            'publicEmailLength' => ['public_email', 'string', 'max' => 255],
            'locationLength' => ['location', 'string', 'max' => 255],
            'websiteLength' => ['website', 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('usuario', 'Name'),
            'public_email' => Yii::t('usuario', 'Email (public)'),
            'location' => Yii::t('usuario', 'Location'),
            'website' => Yii::t('usuario', 'Website'),
            'bio' => Yii::t('usuario', 'Bio'),
            'timezone' => Yii::t('usuario', 'Time zone'),
        ];
    }

    /**
     * Get the User's timezone.
     *
     * @return DateTimeZone
     */
    public function getTimeZone()
    {
        try {
            return new DateTimeZone($this->timezone);
        } catch (Exception $e) {
            return new DateTimeZone(Yii::$app->getTimeZone());
        }
    }

    /**
     * Set the User's timezone.
     *
     * @param DateTimeZone $timezone
     *
     * @throws InvalidParamException
     */
    public function setTimeZone(DateTimeZone $timezone)
    {
        $this->setAttribute('timezone', $timezone->getName());
    }

    /**
     * Get User's local time.
     *
     * @param DateTime|null $dateTime
     *
     * @return DateTime
     */
    public function getLocalTimeZone(DateTime $dateTime = null)
    {
        return $dateTime === null ? new DateTime() : $dateTime->setTimezone($this->getTimeZone());
    }

    /**
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getUser()
    {
        return $this->hasOne($this->getClassMap()->get(User::class), ['id' => 'user_id']);
    }


    /**
     * @return ProfileQuery
     */
    public static function find()
    {
        return new ProfileQuery(static::class);
    }
}
