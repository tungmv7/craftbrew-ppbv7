<?php

/**
 *
 * Cube Framework $Id$ Ubk1G5V/mIbn7v6U1dkv5N8eXFCFpB968gQ8OFqetTw=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * dispatcher class
 */

namespace Cube\Controller;

use Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Response\ResponseInterface,
    Cube\Exception\Dispatcher as DispatcherException,
    Cube\View;

class Dispatcher implements Dispatcher\DispatcherInterface
{

    const DIR_SEPARATOR = '/';
    const DIR_CONTROLLERS = 'Controller';

    /**
     * extended suffix
     */
    const EXTENDED_SUFFIX = 'Extended';

    /**
     *
     * response object
     *
     * @var \Cube\Controller\Response\ResponseInterface
     */
    protected $_response;

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     *
     * get the view object
     *
     * @return \Cube\View
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->setView();
        }

        return $this->_view;
    }

    /**
     * set the view object
     *
     * @param \Cube\View $view
     *
     * @return \Cube\Controller\Dispatcher
     */
    public function setView(View $view = null)
    {
        if (!$view instanceof View) {
            $bootstrap = Front::getInstance()->getBootstrap();
            if ($bootstrap->hasResource('view')) {
                $view = $bootstrap->getResource('view');
            }
            else {
                $view = new View();
            }
        }

        $this->_view = $view;

        return $this;
    }


    /**
     *
     * dispatch method
     *
     * the method will try to run the action method called from the request, and based on the data returned, output the necessary data
     * - if the output is an instance of the View class, then the view application resource is ignored even if instantiated
     * - if the output is an array, we will inject the array in the view resource if it was created or we create a new view object and inject the data
     *
     * @param \Cube\Controller\Request\AbstractRequest    $request
     * @param \Cube\Controller\Response\ResponseInterface $response
     * @param bool                                        $partial set to true if a we have an action helper.
     *
     * @throws \Cube\Exception\Dispatcher
     * @return \Cube\Controller\Response\ResponseInterface $response
     */
    public function dispatch(AbstractRequest $request, ResponseInterface $response, $partial = false)
    {

        $output = null;

        $moduleName = $request->getModule();
        $controllerName = $request->getController();
        $actionName = $request->getAction();

        $className = $moduleName . '\\' . self::DIR_CONTROLLERS . '\\' . $controllerName;

        // try loading an extended controller first
        if (class_exists($className . self::EXTENDED_SUFFIX)) {
            $className .= self::EXTENDED_SUFFIX;
        }

        if (class_exists($className)) {
            $controller = new $className($request, $response);

            if (method_exists($controller, $actionName)) {

                // we catch any output given directly from the action method 
                // and display it after outputting the view file
                ob_start();

                $result = $controller->$actionName();
                $actionOutput = ob_get_clean();

                if ($result instanceof View) {
                    $output = $result->render();
                }
                else if (is_array($result)) {
                    $view = $this->getView();

                    if ($view instanceof View) {
                        // for the action view helper, this will overwrite any action variables with the newer ones
                        $view->setVariables($result);
                    }

                    $viewFileName = $view->getViewFileName();

                    $viewPath = self::DIR_SEPARATOR
                        . $request->normalize($moduleName, true) . self::DIR_SEPARATOR
                        . $request->normalize($controllerName, true) . self::DIR_SEPARATOR
                        . ((!empty($viewFileName)) ?
                            $viewFileName : $request->normalize($actionName, true) . View::FILES_EXTENSION);

                    $view->setViewFileName(null);

                    if ($partial === true) {
                        $output = $view->process($viewPath, true);
                    }
                    else {
                        $view->process($viewPath);
                        $view->setContent($actionOutput);

                        $output = $view->render();
                    }
                }

                $response->appendBody($output);
            }
            else {
                $response->setHeader(' ')
                    ->setResponseCode(404);
                throw new DispatcherException(sprintf("Module: '%s' - action '%s' for the '%s' controller does not exist",
                    $moduleName, $actionName, $className));
            }
        }
        else {
            $response->setHeader(' ')
                ->setResponseCode(404);
            throw new DispatcherException(sprintf("Module: '%s' - controller '%s' does not exist.", $moduleName,
                $className));
        }

        $this->_response = $response;


        return $response;
    }

}

