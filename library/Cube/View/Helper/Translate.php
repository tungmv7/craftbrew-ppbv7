<?php

/**
 *
 * Cube Framework $Id$ 87DIJGWQtasYzOzaac0Fsiajbvh7Eu9OC2YL1sztp58=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\View\Helper;

use Cube\Translate as TranslateObject,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter,
    Cube\Controller\Front;

/**
 * translate view helper
 *
 * Class Translate
 *
 * @package Cube\View\Helper
 */
class Translate extends AbstractHelper
{

    /**
     *
     * translate adapter used by the view helper
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_adapter;

    /**
     *
     * get translation adapter
     *
     * @return TranslateAdapter|null
     */
    public function getAdapter()
    {
        if ($this->_adapter === null) {
            $translate = Front::getInstance()->getBootstrap()->getResource('translate');
            $this->setAdapter($translate);
        }

        return $this->_adapter;
    }

    /**
     *
     * set translation adapter
     *
     * @param mixed $adapter
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setAdapter($adapter)
    {
        if ($adapter instanceof TranslateAdapter) {
            $this->_adapter = $adapter;
        }
        else if ($adapter instanceof TranslateObject) {
            $this->_adapter = $adapter->getAdapter();
        }
        else {
            throw new \InvalidArgumentException("The translation adapter set in the view helper must be of type \Cube\Translate or \Cube\Translate\Adapter.");
        }

        return $this;
    }

    /**
     *
     * get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->getAdapter()->getLocale();
    }

    /**
     *
     * set locale
     *
     * @param string|\Cube\Locale $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->getAdapter()->setLocale($locale);

        return $this;
    }

    /**
     *
     * returns translated string
     *
     * @param string $message
     *
     * @return string
     */
    public function translate($message = null)
    {
        if (!$message) {
            return '';
        }

        return $this->getAdapter()->translate($message);
    }

}

