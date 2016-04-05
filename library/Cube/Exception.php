<?php

/**
 *
 * Cube Framework $Id$ Sb3pNGmb/wfL/5jOqRBgfLs8hiXnEPz9gMA8YnoQEos=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * exceptions class
 */

namespace Cube;

/**
 * Class Exception
 *
 * @package Cube
 */
class Exception extends \Exception
{

    /**
     *
     * construct the exception
     *
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($message = null, $code = null, $previous = null)
    {
        parent::__construct($message, (int)$code, $previous);
    }

    /**
     *
     * display the exception
     *
     * @return string
     */
    public function display()
    {
        return '<div class="alert alert-danger">[Cube\Exception]<br> ' . $this->message . '</div>';
    }
}

