<?php

/**
 * 
 * Cube Framework $Id$ vlGN3nhoQmMj0mXiH02YRo0VqzjMay0YXqkWPVU2Pgc= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * numeric only validator class
 */

namespace Cube\Validate;

class Numeric extends AbstractValidate
{

    protected $_message = "'%s' must contain a numeric value.";

    /**
     * 
     * checks if the variable contains a numeric value
     * 
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {

        if (!preg_match('#^-?\d*\.?\d+$#', $this->_value) && !empty($this->_value)) {
//        if (!preg_match('#^[0-9\.]+$#', $this->_value) && !empty($this->_value)) {
            return false;
        }

        return true;
    }

}

