<?php

/**
 * 
 * Cube Framework $Id$ KFVDaVB9YlXA9uagFV1k3Q9WZ14AUug6xkd/GASFZRY= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * acl role class
 */

namespace Cube\Permissions;

class Role implements RoleInterface
{

    /**
     *
     * role id
     * 
     * @var string
     */
    protected $_id;

    /**
     * 
     * class constructor
     * 
     * @param string $id
     */
    public function __construct($id)
    {
        $this->_id = (string) $id;
    }

    /**
     * 
     * get role id
     * 
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

}

