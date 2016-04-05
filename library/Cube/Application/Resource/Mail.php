<?php

/**
 * 
 * Cube Framework $Id$ HI7C78swHCI0cuZ/9p4YeGj4Lw59u01HK3FEtmIau2g= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * mail resource management class
 */

namespace Cube\Application\Resource;

use Cube\Mail as MailObject;

class Mail extends AbstractResource
{

    /**
     *
     * mail object
     * 
     * @var \Cube\Mail
     */
    protected $_mail;

    /**
     * 
     * initialize mail resource
     * 
     * @return \Cube\Mail
     */
    public function init()
    {
        if (!$this->_mail instanceof MailObject) {
            $this->_mail = new MailObject();

            if (isset($this->_options['mail']['transport'])) {
                $this->_mail->setTransport($this->_options['mail']['transport']);
                $this->_mail->getTransport()->setOptions($this->_options['mail']);

                $view = $this->_mail->getView();

                if (isset($this->_options['mail']['layout_file'])) {
                    $view->setLayout($this->_options['mail']['layout_file']);
                }

                if (isset($this->_options['mail']['layouts_path'])) {
                    $view->setLayoutsPath($this->_options['mail']['layouts_path']);
                }

                if (isset($this->_options['mail']['views_path'])) {
                    $view->setViewsPath($this->_options['mail']['views_path']);
                }
            }
        }

        return $this->_mail;
    }

}

