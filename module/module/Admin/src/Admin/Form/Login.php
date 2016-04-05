<?php

/**
 *
 * PHP Pro Bid $Id$ bB6PcRdmfEf6oMrGe7Y3f0PEViX7TxP+AiC+sCRY/Vo=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * admin login form
 */

namespace Admin\Form;

use Cube\Form;

class Login extends Form
{

    public function __construct($action = null)
    {
        parent::__construct($action);
        $this->setMethod(self::METHOD_POST);

        $translate = $this->getTranslate();

        /* username field */
        $username = $this->createElement('text', 'username');
        $username->setLabel('Username');
        $username->setAttributes(array(
            'class'       => 'form-control',
            'placeholder' => $translate->_('Username'),
        ));
        $username->setRequired();
        $this->addElement($username);

        /* password field */
        $password = $this->createElement('password', 'password');
        $password->setLabel('Password');
        $password->setAttributes(array(
            'class'       => 'form-control',
            'placeholder' => $translate->_('Password'),
        ));
        $this->addElement($password);

        /* submit button */
        $submit = $this->createElement('submit', 'submit');
        $submit->setAttributes(array(
            'class' => 'btn btn-primary btn-lg',
        ));
        $submit->setValue('Proceed');
        $this->addElement($submit);

        $this->setPartial('forms/admin-login.phtml');
    }

}

