<?php

/**
 * 
 * Cube Framework $Id$ CdFqM94prGkeRcAP3cSFll1ng1g0rp2slS2CYwMyzcA= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.6
 */
/**
 * application class
 */

namespace Cube;

use Cube\Loader\Autoloader;

class Application
{
    /**
     *
     * the name of the module bootstrap files
     */

    const BOOTSTRAP = 'Bootstrap';

    /**
     *
     * holds the configuration options array
     * 
     * @var array
     */
    protected $_options;

    /**
     * autoloader object
     *
     * @var \Cube\Loader\Autoloader
     */
    protected $_autoloader;

    /**
     *
     * requested module name
     * 
     * @var \Cube\ModuleManager
     */
    protected $_moduleManager;

    /**
     * 
     * bootstrap
     *
     * @var \Cube\Application\Bootstrap
     */
    protected $_bootstrap;

    /**
     * 
     * returns an instance of the object and creates it if it wasnt instantiated yet
     * 
     * @return \Cube\Application
     */
    private static $_instance;

    /**
     * 
     * class constructor
     * 
     * initialize autoloader
     * 
     * @param array $options     configuration array
     */
    protected function __construct($options = array())
    {
        require_once 'Debug.php';
        Debug::setTimeStart();
        Debug::setMemoryStart();
        Debug::setCpuUsageStart();

        require_once 'Loader/Autoloader.php';

        $this->_autoloader = Autoloader::getInstance()
                ->register();

        $this->setOptions($options);
    }

    /**
     * 
     * initialize application as singleton
     * 
     * @param array $options     configuration array
     * @return \Cube\Application
     */
    public static function init($options = array())
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($options);
        }

        return self::$_instance;
    }

    /**
     * 
     * returns an instance of the application object
     * 
     * @return \Cube\Application
     */
    public static function getInstance()
    {
        return self::$_instance;
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
     * @return \Cube\Application
     */
    public function setOptions(array $options)
    {
        if (!empty($this->_options)) {
            $this->_options = array_replace_recursive(
                array_merge_recursive($this->_options, $options), $options);
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
     * @param string $key
     * @param mixed|null $value
     * @return \Cube\Application
     */
    public function setOption($key, $value = null)
    {

        $this->_options[$key] = $value;

        return $this;
    }

    /**
     * get bootstrap object
     * we will first search for the requested module bootstrap and only if the file doesnt exist will we call the default bootstrap class from the library
     * the bootstrap class will only be created once
     *
     * @return \Cube\Application\Bootstrap
     */
    public function getBootstrap()
    {
        if (!($this->_bootstrap instanceof Application\Bootstrap)) {
            $bootstrap = $this->_moduleManager->getActiveModule() . '\\' . self::BOOTSTRAP;

            if (class_exists($bootstrap)) {
                $this->_bootstrap = new $bootstrap($this);
            }

            if ($this->_bootstrap === null) {
                $this->_bootstrap = new Application\Bootstrap($this);
            }
        }

        return $this->_bootstrap;
    }

    /**
     * initialize module manager and bootstrap application
     *
     * @throws \InvalidArgumentException
     * @return \Cube\Application
     */
    public function bootstrap()
    {
        if (!empty($this->_options['modules'])) {
            $this->_moduleManager = ModuleManager::getInstance()
                    ->setModules($this->_options['modules']);

            $this->setOptions(
                    $this->_moduleManager->getConfig(
                            $this->_moduleManager->getActiveModule()));

            $this->_autoloader->addPaths(
                    $this->_moduleManager->getPaths());
        }
        else {
            throw new \InvalidArgumentException('At least one module needs to be created in order for the application to function.');
        }

        $this->getBootstrap()->bootstrap();

        return $this;
    }

    /**
     * Run the application
     *
     * @return void
     */
    public function run()
    {
        
        $this->getBootstrap()->run();
    }

}

