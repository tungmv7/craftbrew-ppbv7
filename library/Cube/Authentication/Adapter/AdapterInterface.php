<?php

/**
 * 
 * Cube Framework $Id$ /Lyt4RWP2OrKkVjbnBHdyLnznWT1aPsunCE4DR2SB9I= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * authentication adapter interface
 */

namespace Cube\Authentication\Adapter;

interface AdapterInterface
{

    /**
     * 
     * perform an authentication attempt
     * 
     * @return \Cube\Authentication\Result
     */
    public function authenticate();
}

