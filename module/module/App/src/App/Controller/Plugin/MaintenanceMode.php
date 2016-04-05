<?php

/**
 *
 * PHP Pro Bid $Id$ 7JYXMEPAT02fnkYScwRww1ZRqTkaopL9/AWPmmSb31w=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * maintenance mode checker controller plugin class
 */

namespace App\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Authentication,
    Cube\Session,
    Cube\Controller\Front;

class MaintenanceMode extends AbstractPlugin
{

    public function preDispatcher()
    {
        $request = $this->getRequest();

        $module = $request->getModule();

        if ($module != 'Admin') {
            $bootstrap = Front::getInstance()->getBootstrap();
            $settings = $bootstrap->getResource('settings');

            if ($settings['maintenance_mode']) {
                $redirect = true;
                /** @var \Cube\Session $session */
                $config = include __DIR__ . '/../../../../../Admin/config/module.config.php';
                if (array_key_exists('session', $config)) {
                    $session = new Session($config['session']);

                    $storage = new Authentication\Storage\Session($config['session']['namespace'], null, $session);
                    $authentication = new Authentication\Authentication($storage);

                    if ($authentication->hasIdentity()) {
                        $storage = $authentication->getStorage()->read();

                        $role = $storage['role'];

                        if (strcasecmp($role, 'admin') === 0) {
                            $redirect = false;
                        }
                    }

                    if ($redirect) {
                        $request->setController('index')
                            ->setAction('maintenance-mode');
                    }
                }
            }
        }
    }
}

