<?php

/**
 *
 * Cube Framework $Id$ bkyCRgnLirHBElck4GxoPOcOgNQ0za01txGb7/3eTVg=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.6
 */
/**
 * front controller implementation
 */

namespace Cube\Controller;

use Cube\Application,
    Cube\ModuleManager,
    Cube\Controller\Request,
    Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Response\ResponseInterface,
    Cube\Http\Response,
    Cube\Controller\Router,
    Cube\Controller\Dispatcher,
    Cube\Controller\Dispatcher\DispatcherInterface,
    Cube\Debug,
    Cube\Exception\Dispatcher as DispatcherException;

class Front
{

    const ERROR_CONTROLLER = 'error';
    const ERROR_ACTION = 'not-found';

    /**
     *
     * will hold an array of front controller plugin objects
     *
     * @var array
     */
    protected $_plugins;

    /**
     *
     * request object
     *
     * @var \Cube\Controller\Request\AbstractRequest
     */
    protected $_request;

    /**
     *
     * response object
     *
     * @var \Cube\Http\Response
     */
    protected $_response;

    /**
     *
     * router object
     *
     * @var \Cube\Controller\Router
     */
    protected $_router;

    /**
     *
     * dispatcher object
     *
     * @var \Cube\Controller\Dispatcher
     */
    protected $_dispatcher;

    /**
     *
     * holds an instance of the object
     *
     * @var \Cube\Controller\Front
     */
    protected static $_instance;

    /**
     *
     * application config options
     *
     * @var array
     */
    protected $_options;

    /**
     *
     * bootstrap object, in order to be available in the application
     *
     * @var \Cube\Application\Bootstrap
     */
    protected $_bootstrap;

    /**
     *
     * class constructor
     */
    protected function __construct()
    {
        $this->_plugins = new Plugin\Broker();
    }

    /**
     *
     * returns an instance of the object and creates it if it wasnt instantiated yet
     *
     * @return \Cube\Controller\Front
     */
    public static function getInstance()
    {

        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     *
     * register plugin
     *
     * @param \Cube\Controller\Plugin\AbstractPlugin $plugin
     *
     * @return \Cube\Controller\Front
     */
    public function registerPlugin(AbstractPlugin $plugin)
    {
        $this->_plugins->registerPlugin($plugin);

        return $this;
    }

    /**
     *
     * check if the plugin has been registered
     *
     * @param string|AbstractPlugin $plugin
     *
     * @return bool
     */
    public function isRegisteredPlugin($plugin)
    {
        return $this->_plugins->isRegisteredPlugin($plugin);
    }

    /**
     *
     * get the request object, if not set create a new object of type \Cube\Controller\Request
     *
     * @return \Cube\Controller\Request\AbstractRequest
     */
    public function getRequest()
    {
        if (!$this->_request instanceof AbstractRequest) {
            // initialize a routed request
            $this->setRequest(
                new Request());
        }

        return $this->_request;
    }

    /**
     *
     * set the request object
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return \Cube\Controller\Front
     */
    public function setRequest(AbstractRequest $request)
    {
        $this->_request = $request;

        return $this;
    }

    /**
     *
     * get the response object, if not set create a new object of type \Cube\Controller\Response
     *
     * @return \Cube\Controller\Response\ResponseInterface
     */
    public function getResponse()
    {
        if (!($this->_response instanceof ResponseInterface)) {
            $this->setResponse(
                new Response());
        }

        return $this->_response;
    }

    /**
     *
     * set response object
     *
     * @param \Cube\Controller\Response\ResponseInterface $response
     *
     * @return \Cube\Controller\Front
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->_response = $response;

        return $this;
    }

    /**
     *
     * get router object, if not set create a new object of type \Cube\Controller\Router
     *
     * @return \Cube\Controller\Router
     */
    public function getRouter()
    {
        if (!($this->_router instanceof Router\AbstractRouter)) {
            // add routes from module manager singleton
            $routes = ModuleManager::getInstance()->getRoutes();
            $this->setRouter(
                new Router($routes));
        }

        return $this->_router;
    }

    /**
     *
     * set router object
     *
     * @param \Cube\Controller\Router\AbstractRouter $router
     *
     * @return \Cube\Controller\Front
     */
    public function setRouter(Router\AbstractRouter $router)
    {
        $this->_router = $router;

        return $this;
    }

    /**
     *
     * get dispatcher object, if not set create a new object of type \Cube\Controller\Dispatcher
     *
     * @return \Cube\Controller\Dispatcher\DispatcherInterface
     */
    public function getDispatcher()
    {
        if (!($this->_dispatcher instanceof DispatcherInterface)) {
            $this->setDispatcher(
                new Dispatcher());
        }

        return $this->_dispatcher;
    }

    /**
     *
     * set dispatcher object
     *
     * @param \Cube\Controller\Dispatcher\DispatcherInterface $dispatcher
     *
     * @return \Cube\Controller\Front
     */
    public function setDispatcher(DispatcherInterface $dispatcher)
    {
        $this->_dispatcher = $dispatcher;

        return $this;
    }

    /**
     *
     * get options array
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     *
     * set options array
     *
     * @param array $options
     *
     * @return \Cube\Controller\Front
     */
    public function setOptions(array $options)
    {
        if (is_array($this->_options)) {
            $this->_options = array_merge($this->_options, $options);
        }
        else {
            $this->_options = $options;
        }

        return $this;
    }

    /**
     *
     * get a key from the options array
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getOption($key)
    {
        if (isset($this->_options[$key])) {
            return $this->_options[$key];
        }

        return null;
    }

    /**
     *
     * set or unset a key in the options array
     *
     * @param string     $key
     * @param mixed|null $value
     *
     * @return \Cube\Controller\Front
     */
    public function setOption($key, $value = null)
    {
        if ($value === null && isset($this->_options[$key])) {
            unset($this->_options[$key]);
        }
        else {
            $this->_options[$key] = $value;
        }

        return $this;
    }

    /**
     *
     * get the bootstrap object
     *
     * @return \Cube\Application\Bootstrap
     */
    public function getBootstrap()
    {
        return Application::getInstance()->getBootstrap();
    }

    /**
     *
     * dispatch request
     *
     * @param \Cube\Controller\Request\AbstractRequest    $request
     * @param \Cube\Controller\Response\ResponseInterface $response
     */
    public function dispatch(AbstractRequest $request = null, ResponseInterface $response = null)
    {
        if ($request instanceof AbstractRequest) {
            $this->setRequest($request);
        }

        if ($response instanceof ResponseInterface) {
            $this->setResponse($response);
        }

        $request = $this->getRequest();
        $response = $this->getResponse();


        // set request 
        $this->_plugins->setRequest($request)
            ->setResponse($response);

        $router = $this->getRouter();

        // run pre route plugins
        $this->_plugins->preRoute();

        // route request
        $this->_plugins->setRequest(
            $router->route($request));


        // run post route plugins
        $this->_plugins->postRoute();

        // run pre dispatcher plugins
        $this->_plugins->preDispatcher();

        do {
            // set dispatched flag to true
            $request->setDispatched(true);

            // run pre dispatch plugins
            $this->_plugins->preDispatch();

            try {
                $response = $this->getDispatcher()->dispatch($request, $response);
            } catch (DispatcherException $e) {
                $request->setController(self::ERROR_CONTROLLER)
                    ->setAction(self::ERROR_ACTION);

                $request->setDispatched(false);
            }

            // dispatch request
            $this->setResponse(
                $response);

            // run post dispatch plugins
            $this->_plugins->postDispatch();
        } while ($request->isDispatched() !== true);

        // run post dispatcher plugins
        $this->_plugins->postDispatcher();

        $this->setRequest($request)
            ->setResponse($response);
        Debug::setMemoryEnd();
        Debug::setTimeEnd();
        Debug::setCpuUsageEnd();

        $this->getResponse()->send();
    }

}

