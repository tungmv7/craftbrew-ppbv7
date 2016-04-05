<?php

/**
 * 
 * Cube Framework $Id$ bWmv9ujOTu2db8FOSxBCXDe/5HHe8Nf3nh06Adj6bsA= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * phone number validator class
 */

namespace Cube\Validate;

class Phone extends AbstractValidate
{

    protected $_message = "'%s' does not contain a valid phone number.";
    
    /**
     * 
     * checks if the variable contains a valid phone number
     * 
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = str_ireplace(array('+', '-', ' ', '(', ')'), '', $this->_value);
        if (is_numeric($value)) {
            return true;
        }

        return false;
    }

}

