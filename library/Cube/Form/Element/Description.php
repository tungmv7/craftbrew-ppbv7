<?php

/**
 *
 * Cube Framework $Id$ BMHyW2TjyLmq95KF4+3WphIJvfKPat/Es1xsyWwBOOE=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * not actually a form element, instead it will be a simple text description
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Description extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'description';
    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($this->_element, $name);
    }

    /**
     *
     * render the form element
     *
     * @return string
     */
    public function render()
    {
        return $this->getPrefix() . ' '
               . $this->getValue() . ' '
               . $this->getSuffix();
    }

}
