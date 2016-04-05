<?php

/**
 * 
 * Cube Framework $Id$ ysIau9LDJOW7iY9u0OGLkF5nXFx3HMrZiqE/yGmKdkk= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.2
 */
/**
 * alphanumeric only validator class
 * space character is not allowed since version 1.2
 */

namespace Cube\Validate;

class Alphanumeric extends AbstractValidate
{

    protected $_message = "'%s' must contain an alphanumeric value.";
    /**
     * 
     * checks if the variable contains an alphanumeric value
     * 
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        if (!empty($this->_value) && !preg_match('#^[0-9a-zA-Z\_\-]+$#', $this->_value)) {
            return false;
        }

        return true;
    }

}

