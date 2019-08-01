<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Da\User\Event;

use yii\base\Event;
use Da\User\Model\User;
use Da\User\Service\MailService;

/**
 * @property-read string $type
 * @property-read User $user
 * @property-read MailService $mailService
 * @property-read mixed|\Exception $exception
 */
class MailEvent extends Event
{
    const TYPE_WELCOME = 'welcome';
    const TYPE_RECOVERY = 'recovery';
    const TYPE_CONFIRM = 'confirm';
    const TYPE_RECONFIRM = 'reconfirm';
    const TYPE_TICKET = 'ticket';
    const TYPE_NOTIFY = 'notify';
    const TYPE_INVITE = 'invite';

    const EVENT_BEFORE_SEND_MAIL = 'beforeSendMail';
    const EVENT_AFTER_SEND_MAIL = 'afterSendMail';

    /**
     * @var mixed
     */
    protected $type;
    /**
     * @var mixed
     */
    protected $user;
    /**
     * @var mixed
     */
    protected $mailService;
    /**
     * @var mixed
     */
    protected $exception;

    /**
     * @param $type
     * @param User $user
     * @param MailService $mailService
     * @param $exception
     * @param array $config
     */
    public function __construct($type, User $user, MailService $mailService, $exception, $config = [])
    {
        $this->type = $type;
        $this->user = $user;
        $this->mailService = $mailService;
        $this->exception = $exception;

        parent::__construct($config);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getMailService()
    {
        return $this->mailService;
    }

    /**
     * @return mixed
     */
    public function getException()
    {
        return $this->exception;
    }
}
