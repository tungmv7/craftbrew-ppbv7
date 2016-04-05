<?php

/**
 * 
 * Cube Framework $Id$ h4nKTq/JH5hfZUbN+JdurMM2gsB3WmEaPM7Uy8RB+yE= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * no html validator class
 */

namespace Cube\Validate;

class NoHtml extends AbstractValidate
{

    protected $_message = "'%s' cannot contain any html code.";

    /**
     * 
     * checks if the variable contains only text (no html)
     * TODO: doesnt work properly because of request variables special chars rewriting
     * 
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        if ($this->_value != strip_tags($this->_value)) {
//        if (strlen($this->_value) != strlen(strip_tags($this->_value))) {
//        if (!preg_match("#<[^<]+>#", $this->_value) && !empty($this->_value)) {
            return false;
        }

        return true;
    }

}

