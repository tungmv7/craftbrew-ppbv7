<?php

/**
 *
 * PHP Pro Bid $Id$ z8fnTyMmWdaXSzfzHm0VPKLk9qQ2RKd5BxP4sRoRcJ0=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */
/**
 * contact us form
 */

namespace App\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Form\Element\ReCaptcha,
    Cube\Validate;

class Contact extends AbstractBaseForm
{

    const BTN_SUBMIT = 'contact_us';

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

        $fullName = $this->createElement('text', 'name');
        $fullName->setLabel('Full Name')
            ->setAttributes(array(
                'class' => 'form-control input-large'
            ))
            ->setRequired()
            ->addValidator(
                new Validate\NoHtml())
            ->addValidator(
                new Validate\StringLength(array(null, 255)));

        $this->addElement($fullName);

        $email = $this->createElement('text', 'email');
        $email->setLabel('Email Address')
            ->setAttributes(array(
                'class' => 'form-control input-large'
            ))
            ->setRequired()
            ->addValidator(
                new Validate\Email());

        $this->addElement($email);

        $settings = $this->getSettings();

        if ($settings['enable_recaptcha'] && $settings['recaptcha_contact_us']) {
            $captcha = new ReCaptcha('captcha');
            $captcha->setLabel('Captcha Code');

            $this->addElement($captcha);
        }

        $content = $this->createElement('textarea', 'message');
        $content->setLabel('Question / Query')
            ->setAttributes(array(
                'rows'  => 8,
                'class' => 'form-control',
            ))
            ->setRequired()
            ->addValidator(
                new Validate\NoHtml());

        $this->addElement($content);

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

}