<?php

/**
 *
 * PHP Pro Bid $Id$ a7dxlNEZy2E3axs1uJ+pfyroxvqVEtdt/8NROFXvhx4=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */
/**
 * email to friend form
 */

namespace Listings\Form;

use Ppb\Form\AbstractBaseForm,
    Cube\Validate,
    Ppb\Form\Element\ReCaptcha,
    Ppb\Validate\MultipleEmails as MultipleEmailsValidate;

class EmailFriend extends AbstractBaseForm
{

    const BTN_SUBMIT = 'email_friend';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Send',
    );

    /**
     *
     * class constructor
     *
     * @param string $action the form's action
     */
    public function __construct($action = null)
    {
        parent::__construct($action);

        $this->setMethod(self::METHOD_POST);

        $translate = $this->getTranslate();

        $email = $this->createElement('textarea', 'emails');
        $email->setLabel('Email Addresses')
            ->setAttributes(array(
                'rows'        => 4,
                'class'       => 'form-control',
                'placeholder' => $translate->_('You can add multiple email addresses, separated by commas.'),
            ))
            ->setRequired()
            ->addValidator(
                new MultipleEmailsValidate());

        $this->addElement($email);

        $settings = $this->getSettings();

        if ($settings['enable_recaptcha'] && $settings['recaptcha_email_friend']) {
            $captcha = new ReCaptcha('captcha');
            $captcha->setLabel('Captcha Code');

            $this->addElement($captcha);
        }

        $content = $this->createElement('textarea', 'message');
        $content->setLabel('Message')
            ->setAttributes(array(
                'rows'        => 4,
                'class'       => 'form-control',
                'placeholder' => $translate->_('Enter an optional message to be included in the email.'),
            ))
            ->setRequired()
            ->addValidator(
                new Validate\NoHtml());

        $this->addElement($content);

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/email-friend.phtml');
    }

}