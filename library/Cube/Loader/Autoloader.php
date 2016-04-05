<?php

/**
 *
 * Cube Framework $Id$ m6F5IL5tU/V3k0VZ+geozocSqoc/qcTyaEso/EF1LnA=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * autoloader class
 */

namespace Cube\Loader;

class Autoloader
{
    /**
     *
     * location of the framework and application libraries
     */

    const LIBRARIES_PATH = 'library';

    /**
     *
     * we will allow mods to override classes
     */
    const MODS_PATH = 'mods';

    /**
     *
     * holds the array of autoloader paths
     *
     * @var array
     */
    private $_paths = array();

    /**
     *
     * the extension for the files to be autoloaded
     *
     * @var string
     */
    private $_fileExtension = '.php';

    /**
     *
     * holds an instance of the object
     *
     * @var \Cube\Loader\Autoloader
     */
    private static $_instance;

    /**
     * class constructor
     *
     * set the folder path for the default library
     */
    protected function __construct()
    {
        $this->addPaths(array(
            self::LIBRARIES_PATH,
        ));
    }

    /**
     *
     * returns an instance of the object and creates it if it wasnt instantiated yet
     *
     * @return \Cube\Loader\Autoloader
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
     * get autoloader paths
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->_paths;
    }

    /**
     *
     * add multiple autoloader paths
     *
     * @param array $paths
     *
     * @return \Cube\Loader\Autoloader
     */
    public function addPaths($paths = array())
    {
        if (empty($this->_paths)) {
            $this->_paths = (array)$paths;
        } else if (!empty($paths)) {
            foreach ($paths as $path) {
                $this->addPath($path);
            }
        }

        return $this;
    }

    /**
     *
     * add single autoloader path
     *
     * @param string $path
     *
     * @return \Cube\Loader\Autoloader
     */
    public function addPath($path)
    {
        if (!in_array($path, $this->_paths)) {
            array_push($this->_paths, $path);
        }

        return $this;
    }

    /**
     *
     * the method will parse the data from the 'modules' key in the configuration array, and auto load all classes from the
     * folders defined, plus the classes from the include path (in the include path we have included the
     * folder where the framework is located
     *
     * @return $this
     */
    public function register()
    {
        spl_autoload_register(array($this, 'load'));

        return $this;
    }

    /**
     *
     * autoloader method
     *
     * @param string $class
     */
    public function load($class)
    {
        $pathInfo = pathinfo(
            str_replace('\\', DIRECTORY_SEPARATOR, $class));

        $included = false;
        foreach ((array)$this->_paths as $path) {
            $classFile = realpath(__DIR__) . '/../../../'
                . $path . DIRECTORY_SEPARATOR
                . $pathInfo['dirname'] . DIRECTORY_SEPARATOR
                . $pathInfo['filename'] . $this->_fileExtension;

            $extendedClassFile = realpath(__DIR__) . '/../../../'
                . self::MODS_PATH . DIRECTORY_SEPARATOR
                . $path . DIRECTORY_SEPARATOR
                . $pathInfo['dirname'] . DIRECTORY_SEPARATOR
                . $pathInfo['filename'] . $this->_fileExtension;

            if (file_exists($extendedClassFile) && $included === false) {
                include_once $extendedClassFile;
                $included = true;
            }
            if (file_exists($classFile) && $included === false) {
                include_once $classFile;
                $included = true;
            }
        }
    }

}

