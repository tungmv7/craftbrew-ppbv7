<?php

/**
 * 
 * Cube Framework $Id$ Jug7GDubgIgWBpzSj/lgGQnjftGyXs05TIE879t9ljs= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * db expression class
 */

namespace Cube\Db;

class Expr
{

    /**
     *
     * @var string
     */
    protected $_expression;

    /**
     *
     * class constructor 
     * 
     * @param string $expression 
     */
    public function __construct($expression)
    {
        $this->_expression = (string) $expression;
    }

    /**
     * 
     * magic method
     * return the expression
     * 
     * @return string 
     */
    public function __toString()
    {
        return $this->_expression;
    }

}

