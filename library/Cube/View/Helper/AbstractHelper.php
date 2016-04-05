<?php

/**
 *
 * Cube Framework $Id$ CfYizv9pZnS7XQmtHvQ6R9oLUl6e1ryMbnGMJUyejyY=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * view helpers abstract class
 */

namespace Cube\View\Helper;

use Cube\View,
    Cube\Controller\Front,
    Cube\Translate,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter;

abstract class AbstractHelper implements HelperInterface
{

    /**
     * view object
     *
     * @var \Cube\View
     */
    protected $_view = null;

    /**
     *
     * the view partial to be used
     *
     * @var string
     */
    protected $_partial;

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    /**
     *
     * the end tag of the html element
     *
     * @var string
     */
    protected $_endTag = '>';

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
     * @return $this
     */
    public function setView(View $view = null)
    {
        if (!$view instanceof View) {
            $bootstrap = Front::getInstance()->getBootstrap();
            if ($bootstrap->hasResource('view')) {
                $view = $bootstrap->getResource('view');
            } else {
                $view = new View();
            }
        }

        $this->_view = $view;

        return $this;
    }

    /**
     *
     * get the view file
     *
     * @return string
     */
    public function getPartial()
    {
        return $this->_partial;
    }

    /**
     *
     * set the view file
     *
     * @param string $partial
     *
     * @return $this
     */
    public function setPartial($partial)
    {
        $this->_partial = $partial;

        return $this;
    }

    /**
     *
     * get translate adapter
     *
     * @return \Cube\Translate\Adapter\AbstractAdapter
     */
    public function getTranslate()
    {
        if (!$this->_translate instanceof TranslateAdapter) {
            $translate = Front::getInstance()->getBootstrap()->getResource('translate');
            if ($translate instanceof Translate) {
                $this->setTranslate(
                    $translate->getAdapter());
            }
        }

        return $this->_translate;
    }

    /**
     *
     * set translate adapter
     *
     * @param \Cube\Translate\Adapter\AbstractAdapter $translate
     *
     * @return $this
     */
    public function setTranslate(TranslateAdapter $translate)
    {
        $this->_translate = $translate;

        return $this;
    }

}

