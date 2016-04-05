<?php

/**
 * 
 * Cube Framework $Id$ b1YexsZKrrXVzt9VNoJODqdrDEFGZXFHNbMnXYacBwI= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * in array validator class
 */

namespace Cube\Validate;

class InArray extends AbstractValidate
{

    protected $_message = "'%s' was not found in the haystack.";

    /**
     *
     * array to compare the needle against
     * 
     * @var array
     */
    protected $_haystack = array();

    /**
     * 
     * class constructor
     * 
     * initialize the haystack 
     * 
     * @param array $haystack       
     */
    public function __construct(array $haystack = null)
    {
        if ($haystack !== null) {
            $this->setHaystack($haystack);
        }
    }

    /**
     * 
     * get haystack
     * 
     * @return bool
     */
    public function getHaystack()
    {
        return $this->_haystack;
    }

    /**
     * 
     * set haystack
     * 
     * @param array $haystack
     * @return \Cube\Validate\InArray
     */
    public function setHaystack(array $haystack)
    {
        $this->_haystack = $haystack;

        return $this;
    }

    /**
     * 
     * checks if the variable is contained in the haystack submitted
     * 
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        if (!in_array($this->_value, $this->_haystack)) {
            return false;
        }

        return true;
    }

}

