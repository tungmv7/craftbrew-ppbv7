<?php

/**
 * 
 * Cube Framework $Id$ DTlYUHRra7XCT2a4bt+yhi28hcaj5l4ooz1WB9/MDO4= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * not empty validator class (will work with the isRequired form validation method)
 * will use the isValid method from the parent class
 */

namespace Cube\Validate;

class NotEmpty extends AbstractValidate
{

    protected $_message = "'%s' is required and cannot be empty.";
    
    /**
     * 
     * checks if the variable is empty
     * 
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        if (empty($this->_value) && $this->_value !== '0') {
            return false;
        }
        else if (is_array($this->_value)) {
            $array = array_filter($this->_value);
            
            if (empty($array)) {
                return false;
            }
        }
        return true;
    }

}

