<?php

/**
 * 
 * PHP Pro Bid $Id$ RZ7ZPuS3ac1Pu9E8YxVOtJGtVMGK95eYGKK/4W1g1q4=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */

namespace Admin\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front;

class Tax extends AbstractAction
{

    public function init()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $view->controller = 'Tax';
    }

    public function Settings()
    {
        $this->_forward('index', 'settings', null, array('page' => 'tax_settings'));
    }

    public function Configuration()
    {
        $this->_forward('index', 'tables', null, array('table' => 'taxTypes'));
    }

}

