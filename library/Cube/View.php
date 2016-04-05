<?php

/**
 *
 * Cube Framework $Id$ jRpdXSnJnMQKfyM/KB9Xn1dCkWjIjGKLlpXaNknJmck=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * view object
 */

namespace Cube;

use Cube\View\Helper\HelperInterface,
    Cube\Exception,
    Cube\Loader\Autoloader;

class View
{

    const DIR_SEPARATOR = '/';
    const URI_DELIMITER = '/';
    const FILES_EXTENSION = '.phtml';
    const VIEWS_FOLDER = 'view';

    /**
     *
     * the location of the layout file to be used
     *
     * @var string
     */
    protected $_layout;

    /**
     *
     * location where to check for layout files
     *
     * @var string
     */
    protected $_layoutsPath;

    /**
     *
     * location where to check for view files
     *
     * @var string
     */
    protected $_viewsPath;

    /**
     *
     * custom file name to be used
     * if not set, the default name generated for an action will be used
     *
     * @var string
     */
    protected $_viewFileName;

    /**
     *
     * the relative base url of the application
     *
     * @var string
     */
    public $baseUrl;

    /**
     *
     * the variable that will display the views in the layout file
     *
     * @var string
     */
    protected $_content = array();

    /**
     *
     * array of variables that will be forwarded to the layout and view files
     *
     * @var array
     */
    protected $_variables;

    /**
     *
     * instances of view helper objects
     *
     * @var array
     */
    protected $_helpers = array();

    /**
     *
     * @param array $variables
     */
    public function __construct($variables = array())
    {
        $this->setVariables($variables);
        $this->setBaseUrl();
    }

    /**
     *
     * get the contents of the layout variable
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     *
     * set the location of the layout file to be used
     *
     * @param string $layout
     *
     * @return \Cube\View
     */
    public function setLayout($layout)
    {
        if (isset($layout)) {
            $this->_layout = $layout;
        }

        return $this;
    }

    /**
     *
     * clear the layout variable
     *
     * @return \Cube\View
     */
    public function setNoLayout()
    {
        $this->_layout = null;

        return $this;
    }

    /**
     *
     * return the path of the view files
     *
     * @return string
     */
    public function getViewsPath()
    {
        return $this->_viewsPath;
    }

    /**
     *
     * set the view files path
     *
     * @param string $viewsPath
     *
     * @return \Cube\View
     */
    public function setViewsPath($viewsPath)
    {
        if (isset($viewsPath)) {
            $this->_viewsPath = $viewsPath;
        }

        return $this;
    }

    /**
     *
     * set custom view file name to be used when rendering
     *
     * @param string $viewFileName
     *
     * @return \Cube\View
     */
    public function setViewFileName($viewFileName)
    {
        $this->_viewFileName = $viewFileName;

        return $this;
    }

    /**
     *
     * get custom view file name
     *
     * @return string
     */
    public function getViewFileName()
    {
        return $this->_viewFileName;
    }


    /**
     *
     * return the path of the layout files
     *
     * @return string
     */
    public function getLayoutsPath()
    {
        return $this->_layoutsPath;
    }

    /**
     *
     * set the layout files path
     *
     * @param string $layoutsPath
     *
     * @return \Cube\View
     */
    public function setLayoutsPath($layoutsPath)
    {
        if (isset($layoutsPath)) {
            $this->_layoutsPath = $layoutsPath;
        }

        return $this;
    }

    /**
     *
     * set base url
     *
     * @return \Cube\View
     */
    public function setBaseUrl()
    {
        $this->baseUrl = rtrim(
            dirname($_SERVER['SCRIPT_NAME']), '/\\');

        return $this;
    }

    /**
     *
     * get layout content
     *
     * @return string
     */
    public function getContent()
    {
        return implode('', $this->_content);
    }

    /**
     *
     * add content to the layout content array
     *
     * @param array $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->_content[] = $content;

        return $this;
    }

    /**
     *
     * clear content array
     *
     * @return \Cube\View
     */
    public function clearContent()
    {
        $this->_content = array();

        return $this;
    }

    /**
     *
     * get view variables array
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->_variables;
    }

    /**
     *
     * set multiple view variables
     *
     * @param array $variables
     *
     * @return \Cube\View
     */
    public function setVariables(array $variables)
    {
        foreach ($variables as $key => $value) {
            $this->setVariable($key, $value);
        }

        return $this;
    }

    /**
     *
     * clear view object variables
     *
     * @return \Cube\View
     */
    public function clearVariables()
    {
        $this->_variables = array();

        return $this;
    }

    /**
     *
     * get all instantiated view helpers
     *
     * @return array
     */
    public function getHelpers()
    {
        return $this->_helpers;
    }

    /**
     *
     * return a view helper
     *
     * @param string $name
     *
     * @throws \DomainException
     * @return \Cube\View\Helper\HelperInterface
     */
    public function getHelper($name)
    {
        if (array_key_exists($name, $this->_helpers)) {
            return $this->_helpers[$name];
        }
        else {
            $className = '\\' . __NAMESPACE__ . '\\View\\Helper\\' . ucfirst($name);

            if (class_exists($className)) {
                $reflect = new \ReflectionClass($className);
                $instance = $reflect->newInstanceArgs();

                $helper = $instance->setView($this);

                $this->setHelper($name, $helper);

                return $helper;
            }
            else {
                throw new \DomainException(sprintf("A helper with the name '%s' does not exist.", ucfirst($name)));
            }
        }
    }

    /**
     *
     * set a new view helper
     *
     * @param string                            $name
     * @param \Cube\View\Helper\HelperInterface $helper
     *
     * @return \Cube\View
     * @throws \InvalidArgumentException
     */
    public function setHelper($name, $helper)
    {
        if (!array_key_exists($name, $this->_helpers)) {
            if ($helper instanceof HelperInterface) {
                $this->_helpers[$name] = $helper;
            }
            else {
                throw new \InvalidArgumentException(
                    sprintf("The view helper with the name '%s' must be an instance of \Cube\View\Helper\HelperInterface.",
                        $name));
            }
        }
//        else {
//            throw new \InvalidArgumentException(
//                sprintf("A view helper with the name '%s' already exists", $name));
//        }

        return $this;
    }

    /**
     *
     * check if a view helper has been set
     *
     * @param string $name the name of the registered helper
     *
     * @return bool
     */
    public function isHelper($name)
    {
        if (array_key_exists($name, $this->_helpers)) {
            return true;
        }

        return false;
    }

    /**
     *
     * set single view variable
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return \Cube\View
     */
    public function setVariable($key, $value)
    {
        $this->_variables[$key] = $value;

        return $this;
    }

    /**
     *
     * processes a single view file and saves the output in the views variable in array format
     * or it returns the output to a view helper
     *
     * (additions)
     * > if the active theme contains the view file, then that file will be processed
     * instead of the default module view file
     * > if the view file isn't found in the theme or the active module,
     * check all modules from the application before returning a file now found error
     * > (1.4) each check will first check in the mods folder
     * PROCESSING ORDER:
     * - absolute name
     * - themes folder
     * - modules views folder
     *
     * @param string $file
     * @param bool   $partial if partial, the output is not saved in the output array
     *
     * @return string
     */
    public function process($file, $partial = false)
    {
        $location = null;

        $baseFile = ltrim($file, self::DIR_SEPARATOR);

        $locations = array(
            $file, // <- absolute path to file (wont work for mods etc), nor for themes
            $this->_layoutsPath . self::DIR_SEPARATOR . $baseFile, // <- theme specific file (needs relative path)
        );

        $moduleManager = ModuleManager::getInstance();

        $modulePaths = $moduleManager->getPaths();
        $activeModule = $moduleManager->getActiveModule();

        if ($activeModule) {
            $modulePaths = array($activeModule => $modulePaths[$activeModule]) + $modulePaths;
        }

        foreach ($modulePaths as $path) {
            if ($path) {
                $fileLocation = str_replace(DIRECTORY_SEPARATOR
                        . ModuleManager::MODULE_FILES, '', $path)
                    . DIRECTORY_SEPARATOR . self::VIEWS_FOLDER
                    . DIRECTORY_SEPARATOR . $baseFile;

                array_push($locations, Autoloader::MODS_PATH . self::DIR_SEPARATOR . $fileLocation);
                array_push($locations, $fileLocation);
            }
        }

        foreach ($locations as $loc) {
            if (file_exists($loc) && $location === null) {
                $location = $loc;

            }
        }

        try {
            if ($location !== null) {
                @extract($this->_variables);

                ob_start();

                include $location;
                $output = ob_get_clean();

                if ($partial === false) {
                    $this->setContent($output);
                }
                else {
                    return $output;
                }
            }
            else {
                throw new Exception(
                    sprintf("The view file '%s' could not be found.", $file));
            }
        } catch (Exception $e) {
            $this->setContent($e->display());

        }

        return '';
    }

    /**
     *
     * renders the layout and returns the output buffer
     *
     * @param null $layout
     *
     * @internal param string $name the name of the layout to process
     * @return string|null
     */
    public function render($layout = null)
    {
        if ($layout === null) {
            $layout = ltrim($this->_layout, self::DIR_SEPARATOR);
        }

        ob_start();

        if (@is_file($layout)) {
            $layout = $this->_layout;
        }
        else if (@is_file($this->_layoutsPath . self::DIR_SEPARATOR . $layout)) {
            $layout = $this->_layoutsPath . self::DIR_SEPARATOR . $layout;
        }
        else {
            $layout = null;
        }

        if ($layout !== null) {
            require $layout;

            return ob_get_clean();
        }
        else {
            echo $this->getContent();
        }
    }

    /**
     *
     * get magic method, enables <code> echo $view->name </code>
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function get($name)
    {
        $method = 'get' . ucfirst($name);

        if (method_exists($this, $method)) {
            return $this->$method();
        }
        else if (isset($this->_variables[$name])) {
            return $this->_variables[$name];
        }

        return null;
    }

    /**
     *
     * set page attributes (magic method): enables <code>$view->name = $value</code>
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return \Cube\View
     */
    public function set($name, $value)
    {
        $method = 'set' . ucfirst($name);

        if (method_exists($this, $method)) {
            $this->$method($value);
        }
        else {
            $this->_variables[$name] = $value;
        }

        return $this;
    }

    /**
     *
     * get magic method, proxy to $this->get($name)
     *
     * @param string $name
     *
     * @return string|null
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     *
     * set magic method, proxy for $this->set($name, $value) method
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     *
     * call magic method, used for calling view helpers
     * custom helpers need to be registered with the view in the bootstrap
     * create a proxy for the translate view helper, called <code>$this->_($message)</code>
     *
     * @param string $name      the name of the view helper
     * @param array  $arguments the arguments accepted by the helper in array format
     *
     * @return view helper                          return the view helper method with the same name as the view helper
     */
    public function __call($name, $arguments)
    {
        if (strcmp($name, '_') === 0) {
            $name = 'translate';
        }

        $helper = $this->getHelper($name);

        return call_user_func_array(
            array($helper, $name), $arguments);
    }

}

