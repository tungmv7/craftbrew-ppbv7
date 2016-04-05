<?php

/**
 * 
 * Cube Framework $Id$ jRpdXSnJnMQKfyM/KB9Xn1dCkWjIjGKLlpXaNknJmck= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * view resource management class
 */

namespace Cube\Application\Resource;

use Cube\View as ViewObject;

class View extends AbstractResource
{

    /**
     *
     * view object
     * 
     * @var \Cube\View
     */
    protected $_view;
    
    /**
     * 
     * get view object
     * 
     * @return \Cube\View
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * 
     * set view object
     * 
     * @param \Cube\View $view
     * @return \Cube\Application\Resource\View
     * @throws \InvalidArgumentException
     */
    public function setView($view)
    {
        if (!$view instanceof ViewObject) {
            throw new \InvalidArgumentException("\Cube\Application\Resource\View requires an object of type \Cube\View");
        }
        
        $this->_view = $view;
        
        return $this;
    }

    /**
     * 
     * initialize view object based on resource settings
     * 
     * @return \Cube\View
     */
    public function init()
    {
        if (!$this->_view instanceof ViewObject) {
            $view = new ViewObject();
            $view->setLayout($this->_options['view']['layout_file'])
                    ->setLayoutsPath($this->_options['view']['layouts_path'])
                    ->setViewsPath($this->_options['view']['views_path']);
            
            $this->setView($view);
        }

        return $this->getView();
    }
}

