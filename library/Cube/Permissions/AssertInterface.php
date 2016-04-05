<?php

/**
 * 
 * Cube Framework $Id$ fi6dbBh/K50hKuvx5bVb0qxaUk9EzK407f9oh60365Q= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * acl assertion interface
 * used for custom acl assertions
 */

namespace Cube\Permissions;

interface AssertInterface
{

    /**
     * 
     * set up a custom acl assertion
     * 
     * @param \Cube\Permissions\Acl $acl
     * @param \Cube\Permissions\RoleInterface $role
     * @param \Cube\Permissions\ResourceInterface $resource
     * @param string $privilege
     * @return bool
     */
    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null);
}

