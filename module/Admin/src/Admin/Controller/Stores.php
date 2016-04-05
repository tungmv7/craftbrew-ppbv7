<?php

/**
 *
 * PHP Pro Bid $Id$ AO9tO5D8xetzZpD/pieLb6HdtUYoJGrKpjYNNmpXuMU=
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

class Stores extends AbstractAction
{

    public function init()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $view->controller = 'Stores';
    }

    public function Settings()
    {
        $this->_forward('index', 'settings', null, array('page' => 'stores_settings'));
    }

    public function Subscriptions()
    {
        $this->_forward('index', 'tables', null, array('table' => 'storesSubscriptions'));
    }

    public function Management()
    {
        $this->_helper->redirector()->redirect('browse', 'users', null, array('view' => 'site', 'filter' => 'stores'));
    }

}

