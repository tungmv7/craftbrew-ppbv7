<?php

/**
 * 
 * PHP Pro Bid $Id$ J1PU/OjrT/R6uqbCrYJdE0zsGuJj+RPChdSCpQluRXQ=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.2
 */
/**
 * acl controller plugin class
 */

namespace Admin\Controller\Plugin;

use Cube\Permissions\Acl as PermissionsAcl,
    Cube\Controller\Plugin\AbstractPlugin;

class Acl extends AbstractPlugin
{

    /**
     *
     * acl object
     * 
     * @var \Cube\Permissions\Acl
     */
    protected $_acl;

    /**
     *
     * user role
     * 
     * @var string
     */
    protected $_role;

    /**
     * 
     * class constructor
     * 
     * @param \Cube\Permissions\Acl $acl    the acl to use
     * @param string $role                  the role of the user
     */
    public function __construct(PermissionsAcl $acl, $role)
    {
        $this->_acl = $acl;
        $this->_role = (string) $role;
    }

    public function preDispatcher()
    {
        $request = $this->getRequest();

        $controller = $request->getController();
        $action = $request->getAction();

        if (!$this->_acl->hasResource($controller)) {
            $this->getResponse()
                    ->setHeader(' ')
                    ->setResponseCode(404);

            $controller = 'error';
            $action = 'not-found';

            $request->setController($controller)
                    ->setAction($action);
        }
        else if (!$this->_acl->isAllowed($this->_role, $controller, $action)) {
            $controller = 'index';
            $action = ($this->_role === 'Guest') ? 'login' : 'index';

            $request->setController($controller)
                    ->setAction($action)
                    ->setParam('logged', '1');
        }
    }

}

