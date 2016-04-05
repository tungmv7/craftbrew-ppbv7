<?php

/**
 *
 * Cube Framework $Id$ GoS4pXD//bEV0etlArsuMMQcmZHvF2U5IOIgr5/WeCc=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2016 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.7
 */
/**
 * sessions handler class
 */

namespace Cube;

use Cube\Controller\Request;

class Session
{

    const COOKIE_DAYS = 30;

    /**
     *
     * session variables namespace/prefix
     *
     * @var string
     */
    protected $_namespace = null;

    /**
     *
     * cookie secret string, used for encrypting cookie values
     *
     * @var string
     */
    protected $_secret;

    /**
     *
     * class constructor
     *
     * @param array $options options array set for initializing the object
     */
    public function __construct($options = array())
    {
        $this->_namespace = isset($options['namespace']) ? $options['namespace'] : null;
        $this->_secret = isset($options['secret']) ? $options['secret'] : null;

        $this->start();
    }

    /**
     *
     * get the namespace of the session object
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     *
     * set the namespace of the session object
     *
     * @param string $namespace
     * @return \Cube\Session
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = (string)$namespace;

        return $this;
    }

    /**
     *
     * get cookie secret string
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->_secret;
    }

    /**
     *
     * set cookie secret string
     *
     * @param string $secret
     * @return \Cube\Session
     */
    public function setSecret($secret)
    {
        $this->_secret = (string)$secret;

        return $this;
    }

    /**
     *
     * get the selected session variable
     *
     * @param string $name
     * @return mixed|null          return variable or null if variable is not set
     */
    public function get($name)
    {
        if (isset($_SESSION[$this->_namespace][$name])) {
            return $_SESSION[$this->_namespace][$name];
        }

        return null;
    }

    /**
     *
     * set a new session variable
     *
     * @param string $name
     * @param mixed  $value
     * @return \Cube\Session
     */
    public function set($name, $value)
    {
        $_SESSION[$this->_namespace][$name] = $value;

        return $this;
    }

    /**
     *
     * get magic method, proxy to $this->get($name)
     *
     * @param string $name
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
     * delete a session variable
     *
     * @param string $name
     * @return \Cube\Session
     */
    public function unregister($name)
    {
        if (isset($_SESSION[$this->_namespace][$name])) {
            unset($_SESSION[$this->_namespace][$name]);
        }

        return $this;
    }

    /**
     *
     * get the readable value of a cookie or null if the cookie doesnt exist
     *
     * @param string $key
     * @return string|null
     */
    public function getCookie($key)
    {
        $key = $this->getCookieKey($key);

        if (isset($_COOKIE[$key])) {
            $crypt = new Crypt();
            $crypt->setKey($this->_secret);

            return $crypt->decrypt($_COOKIE[$key]);
        }

        return null;
    }

    /**
     *
     * create a cookie
     * 1.7 - save cookie as superglobal too, so that it can be used immediately
     *
     * @param string $key   cookie name
     * @param string $value cookie value
     * @param string $path  cookie path
     * @return \Cube\Session
     */
    public function setCookie($key, $value, $path = null)
    {
        $key = $this->getCookieKey($key);
        $value = $this->setCookieValue($value);

        $expirationDate = time() + self::COOKIE_DAYS * 24 * 60 * 60;

        if ($path == null) {
            $request = new Request();
            $path = $request->getBaseUrl(true);
        }

        setcookie($key, $value, $expirationDate, $path);
        $_COOKIE[$key] = $value;

        return $this;

    }

    /**
     *
     * return the set name of a cookie variable (namespace + name)
     *
     * @param string $key
     * @return string
     */
    public function getCookieKey($key)
    {
        return $this->_namespace . $key;
    }

    /**
     *
     * set the cookie value
     *
     * @param string $value
     * @return string
     */
    public function setCookieValue($value)
    {
        $crypt = new Crypt();
        $crypt->setKey($this->_secret);

        return $crypt->encrypt($value);
    }

    /**
     *
     * delete a cookie
     *
     * @param string $name  cookie name
     * @param string $path  cookie path
     * @return \Cube\Session
     */
    public function unsetCookie($name, $path = null)
    {
        if ($path === null) {
            $request = new Request();
            $path = $request->getBaseUrl();
        }

        setcookie($this->getCookieKey($name), null, -1, $path);

        return $this;
    }

    /**
     *
     * start session
     */
    public function start()
    {
        if (session_id() == '') {
            session_start();
        }
    }

    /**
     *
     * destroy the current session
     */
    public function destroy()
    {
        session_destroy();
    }

}
