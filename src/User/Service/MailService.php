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
use yii\mail\BaseMailer;
use yii\mail\MailerInterface;
use Da\User\Contracts\ServiceInterface;

class MailService implements ServiceInterface
{
    /**
     * @var string
     */
    protected $viewPath = '@Da/User/resources/views/mail';

    /**
     * @var mixed
     */
    protected $type;
    /**
     * @var mixed
     */
    protected $from;
    /**
     * @var mixed
     */
    protected $to;
    /**
     * @var mixed
     */
    protected $subject;
    /**
     * @var mixed
     */
    protected $view;
    /**
     * @var array
     */
    protected $params = [];
    /**
     * @var mixed
     */
    protected $mailer;

    /**
     * MailService constructor.
     *
     * @param string                     $type    the mailer type
     * @param string                     $from    from email account
     * @param string                     $to      to email account
     * @param string                     $subject the email subject
     * @param string                     $view    the view to render mail
     * @param array                      $params  view parameters
     * @param BaseMailer|MailerInterface $mailer  mailer interface
     */
    public function __construct($type, $from, $to, $subject, $view, array $params, MailerInterface $mailer)
    {
        $this->type = $type;
        $this->from = $from;
        $this->to = $to;
        $this->subject = $subject;
        $this->view = $view;
        $this->params = $params;
        $this->mailer = $mailer;
        $this->mailer->setViewPath($this->viewPath);
        $this->mailer->getView()->theme = Yii::$app->view->theme;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setViewParam($name, $value)
    {
        $this->params[$name] = $value;

        return $this;
    }

    /**
     * gets mailer type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function run()
    {
        return $this->mailer
                    ->compose(['html' => $this->view, 'text' => "text/{$this->view}"], $this->params)
                    ->setFrom($this->from)
                    ->setTo($this->to)
                    ->setSubject($this->subject)
                    ->send();
    }
    /**
     * @return mixed
     */
    public function queue()
    {

        return Yii::$app->mailqueue
                        ->compose(['html' => $this->view, 'text' => "text/{$this->view}"], $this->params)
                        ->setFrom($this->from)
                        ->setTo($this->to)
                        ->setSubject($this->subject)
                        ->queue();
    }
}
