<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Da\User\Service;

use Da\User\Contracts\MailChangeStrategyInterface;
use Da\User\Contracts\ServiceInterface;
use Da\User\Model\Token;
use Da\User\Model\User;
use Da\User\Query\TokenQuery;
use Da\User\Query\UserQuery;
use Da\User\Traits\ModuleAwareTrait;
use Yii;

class EmailChangeService implements ServiceInterface
{
    use ModuleAwareTrait;

    protected $code;
    protected $model;
    protected $tokenQuery;
    protected $userQuery;

    public function __construct($code, User $model, TokenQuery $tokenQuery, UserQuery $userQuery)
    {
        $this->code = $code;
        $this->model = $model;
        $this->tokenQuery = $tokenQuery;
        $this->userQuery = $userQuery;
    }

    public function run()
    {
        /** @var Token $token */
        $token = $this->tokenQuery
            ->whereUserId($this->model->id)
            ->whereCode($this->code)
            ->whereIsTypes([Token::TYPE_CONFIRM_NEW_EMAIL, Token::TYPE_CONFIRM_OLD_EMAIL])
            ->one();

        if ($token === null || $token->getIsExpired()) {


            return false;
        }
        $token->delete();
        if (empty($this->model->unconfirmeEmail)) {
        } elseif ($this->userQuery->whereEmail($this->model->unconfirmeEmail)->exists() === false) {
            if ($this->getModule()->emailChangeStrategy === MailChangeStrategyInterface::TYPE_SECURE) {
                if ($token->type === Token::TYPE_CONFIRM_NEW_EMAIL) {
                    $this->model->flags |= User::NEW_EMAIL_CONFIRMED;
                } elseif ($token->type === Token::TYPE_CONFIRM_OLD_EMAIL) {
                    $this->model->flags |= User::OLD_EMAIL_CONFIRMED;

                }
            }
            if ((($this->model->flags & User::NEW_EMAIL_CONFIRMED) && ($this->model->flags & User::OLD_EMAIL_CONFIRMED))
                || $this->getModule()->emailChangeStrategy === MailChangeStrategyInterface::TYPE_DEFAULT
            ) {
                $this->model->email = $this->model->unconfirmeEmail;
                $this->model->unconfirmedEmail = null;
            }

            return $this->model->save(false);
        }

        return false;
    }
}
