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

use Yii;
use Exception;
use Da\User\Model\User;
use Da\User\Event\UserEvent;
use Da\User\Factory\TokenFactory;
use Da\User\Helper\SecurityHelper;
use Da\User\Traits\MailAwareTrait;
use yii\base\InvalidCallException;
use Da\User\Traits\ModuleAwareTrait;
use Da\User\Contracts\ServiceInterface;

class UserInviteService implements ServiceInterface
{
    use MailAwareTrait;
    use ModuleAwareTrait;

    /**
     * @var mixed
     */
    protected $model;
    /**
     * @var mixed
     */
    protected $securityHelper;
    /**
     * @var mixed
     */
    protected $mailService;

    /**
     * @param User $model
     * @param MailService $mailService
     * @param SecurityHelper $securityHelper
     */
    public function __construct(User $model, MailService $mailService, SecurityHelper $securityHelper)
    {
        $this->model = $model;
        $this->mailService = $mailService;
        $this->securityHelper = $securityHelper;
    }

    /**
     * @throws InvalidCallException
     * @throws \yii\db\Exception
     * @return bool
     *
     */
    public function run()
    {
        $model = $this->model;

        if ($model->getIsNewRecord() === false) {
            throw new InvalidCallException('Cannot create a new user from an existing one.');
        }

        $transaction = $model::getDb()->beginTransaction();

        try {
            $model->confirmed_at = $this->getModule()->enableEmailConfirmation ? null : time();
            $model->password = !empty($model->password)
            ? $model->password
            : $this->securityHelper->generatePassword(8);

            $event = $this->make(UserEvent::class, [$model]);
            $model->trigger(UserEvent::EVENT_BEFORE_CREATE, $event);

            if (!$model->save()) {
                $transaction->rollBack();
                return false;
            }
            $auth = Yii::$app->authManager;
            $client_role = $auth->getRole('client');
            $auth->assign($client_role, $model->id);
            if ($this->getModule()->enableEmailConfirmation) {
                $token = TokenFactory::makeConfirmationToken($model->id);
            }

            if (isset($token)) {
                $this->mailService->setViewParam('token', $token);
            }

            $model->trigger(UserEvent::EVENT_AFTER_CREATE, $event);
            if (!$this->sendMail($model)) {
                $error_msg = Yii::t(
                    'usuario',
                    'Error sending welcome message to "{email}". Please try again later.',
                    ['email' => $model->email]
                );
                // from web display a flash message (if enabled)
                if ($this->getModule()->enableFlashMessages == true && is_a(Yii::$app, yii\web\Application::class)) {
                    Yii::$app->session->setFlash(
                        'warning',
                        $error_msg
                    );
                }
                // if we're from console add an error to the model in order to return an error message
                if (is_a(Yii::$app, yii\console\Application::class)) {
                    $model->addError("username", $error_msg);
                }
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), 'usuario');

            return false;
        }
    }
}
