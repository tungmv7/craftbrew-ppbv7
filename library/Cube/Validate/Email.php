<?php

/**
 * 
 * Cube Framework $Id$ 4bFWKjEF11Ysvy43JTQXu4Lfj0nOca6zAR3SgKS3P2Q= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * email address validator class
 */

namespace Cube\Validate;

class Email extends AbstractValidate
{

    protected $_message = "'%s' does not contain a valid email address.";
    
    /**
     * 
     * checks if the variable contains a valid email address
     * 
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        if (!preg_match('#^\S+@\S+\.\S+$#', $this->_value)) {
            return false;
        }

        return true;
    }

}

