<?php

/**
 * 
 * Cube Framework $Id$ yJor0IO2LJxd5TboKaN9NZ8O+ytHiwaJsKKxJiKjaLw= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * acl resource class
 */

namespace Cube\Permissions;

class Resource implements ResourceInterface
{

    /**
     *
     * resource id
     * 
     * @var string
     */
    protected $_id;

    /**
     * 
     * class constructor
     * 
     * @param string $id    the resource id/name
     */
    public function __construct($id)
    {
        $this->_id = (string) $id;
    }

    /**
     * 
     * get resource id
     * 
     * @return string       the resource id/name
     */
    public function getId()
    {
        return $this->_id;
    }

}

