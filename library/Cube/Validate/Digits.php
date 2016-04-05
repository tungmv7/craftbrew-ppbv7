<?php

/**
 * 
 * Cube Framework $Id$ H1QkZmPH8YTECefV4s6XcWV5ZkIzRJkRdSgg1tNmyrA= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * digits only validator class
 */

namespace Cube\Validate;

class Digits extends AbstractValidate
{
    
    protected $_message = "'%s' can only contain digits.";

    /**
     * 
     * checks if the variable contains digits only
     * 
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        if (!empty($this->_value) && !preg_match('#^[0-9]+$#', $this->_value)) {
            return false;
        }

        return true;
    }

}

