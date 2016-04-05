<?php

/**
 *
 * Cube Framework $Id$ uftfNMRT5ETtdn65XAj557chksKbMCuymfUnN7eHE2I=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * redirect action helper
 */

namespace Cube\Controller\Action\Helper;

use Cube\Controller\Front;

class Redirector extends AbstractHelper
{
    /**
     *
     * http redirect code
     */

    const REDIRECT_CODE = 302;

    const ERROR_CONTROLLER = 'error';
    const ERROR_ACTION = 'not-found';

    /**
     *
     * redirect the action to the url requested and exit
     *
     * @param string $url
     *
     * @return void
     */
    public function gotoUrl($url)
    {
        $this->getResponse()
            ->setRedirect($url, self::REDIRECT_CODE)
            ->sendHeaders();
        exit();
    }

    /**
     *
     * redirect internally to the selected set of action/controller/module/params
     *
     * @param string $action     requested action
     * @param string $controller optional. requested controller or current controller if null
     * @param string $module     optional. requested module or current module if null
     * @param array  $params
     *
     * @return void
     */
    public function redirect($action, $controller = null, $module = null, $params = null)
    {
        $request = $this->getRequest();
        if ($controller === null) {
            $controller = $request->getController();
        }

        if ($module === null) {
            $module = $request->getModule();
        }

        if ($params === null) {
            $params = $request->getQuery();
        }

        $params['action'] = $action;
        $params['controller'] = $controller;
        $params['module'] = $module;

        $router = Front::getInstance()->getRouter();
        $url = $router->assemble($params);

        $this->gotoUrl($url);
    }

    /**
     *
     * redirect to the not found action from the error controller in the default module
     *
     * @return void
     */
    public function notFound()
    {
        $modules = Front::getInstance()->getOption('modules');

        $this->redirect(self::ERROR_ACTION, self::ERROR_CONTROLLER, $modules[0], array());
    }

}

