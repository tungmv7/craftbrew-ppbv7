<?php

/**
 *
 * Cube Framework $Id$ J1PU/OjrT/R6uqbCrYJdE0zsGuJj+RPChdSCpQluRXQ=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\Permissions;

/**
 * access control list management class
 *
 * Class Acl
 *
 * @package Cube\Permissions
 */
class Acl
{
    /**
     * rule type = allow
     */

    const TYPE_ALLOW = 'ALLOW';
    /**
     * rule type = deny
     */
    const TYPE_DENY = 'DENY';

    /**
     * rule operation = add
     */
    const OPERATION_ADD = 'ADD';

    /**
     * rule operation = remove
     */
    const OPERATION_REMOVE = 'REMOVE';

    /**
     *
     * array of roles
     *
     * @var array
     */
    protected $_roles = array();

    /**
     *
     * array of resources
     *
     * @var array
     */
    protected $_resources = array();

    /**
     *
     * array of rules
     *
     * array format:
     *
     *      array($resourceId => array(
     *          $roleId = array(
     *              array(
     *                  'name' => $privilegeName|null (all privileges)
     *                  'type' => ALLOW|DENY
     *              )
     *          )
     *      )
     *
     * @var array
     */
    protected $_rules = array();

    /**
     *
     * add a role to the roles array
     *
     * @param \Cube\Permissions\RoleInterface $role
     * @param mixed                           $parents
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \DomainException
     * @return $this
     */
    public function addRole(RoleInterface $role, $parents = null)
    {
        $roleId = $role->getId();

        $roleParents = array();

        if ($this->hasRole($role)) {
            throw new \InvalidArgumentException(
                sprintf("The role with the id '%s' already exists.", $roleId));
        }

        if ($parents !== null) {
            if (!is_array($parents)) {
                $parents = array($parents);
            }

            foreach ((array)$parents as $parent) {
                if (!$parent instanceof RoleInterface) {
                    throw new \RuntimeException("All role parents must be of type \Cube\Permissions\RoleInterface");
                }

                $roleParentId = $parent->getId();

                if ($roleId === $roleParentId) {
                    throw new \DomainException(
                        sprintf("Cannot set a parent role as itself, role id '%s'.", $roleId));
                }

                $roleParents[$roleParentId] = $parent;

                $this->_roles[$roleParentId]['children'][$roleId] = $role;
            }
        }

        $this->_roles[$roleId] = array(
            'instance' => $role,
            'parents'  => $roleParents,
            'children' => array()
        );

        return $this;
    }

    /**
     *
     * check if a role exists in the acl
     *
     * @param string|\Cube\Permissions\RoleInterface $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        if ($role instanceof RoleInterface) {
            $roleId = $role->getId();
        }
        else {
            $roleId = (string)$role;
        }

        return isset($this->_roles[$roleId]);
    }

    /**
     *
     * return the role instance
     *
     * @param string|\Cube\Permissions\RoleInterface $role
     *
     * @return \Cube\Permissions\RoleInterface
     * @throws \InvalidArgumentException
     */
    public function getRole($role)
    {
        if ($role instanceof RoleInterface) {
            $roleId = $role->getId();
        }
        else {
            $roleId = (string)$role;
        }

        if ($this->hasRole($role) === false) {
            throw new \InvalidArgumentException(
                sprintf("The role '%s' was not found.", $roleId));
        }

        return $this->_roles[$roleId]['instance'];
    }

    /**
     *
     * remove a role from the roles array, including parent and child dependencies
     *
     * @param string|\Cube\Permissions\RoleInterface $role
     *
     * @return $this
     */
    public function removeRole($role)
    {
        $roleId = $this->getRole($role)->getId();

        foreach ($this->_roles[$roleId]['children'] as $childId => $child) {
            unset($this->_roles[$childId]['parents'][$roleId]);
        }
        foreach ($this->_roles[$roleId]['parents'] as $parentId => $parent) {
            unset($this->_roles[$parentId]['children'][$roleId]);
        }

        unset($this->_roles[$roleId]);

        return $this;
    }

    /**
     *
     * reset roles array
     *
     * @return $this
     */
    public function removeAllRoles()
    {
        $this->_roles = array();

        foreach ($this->_rules['allResources']['byRoleId'] as $roleIdCurrent => $rules) {
            unset($this->_rules['allResources']['byRoleId'][$roleIdCurrent]);
        }
        foreach ($this->_rules['byResourceId'] as $resourceIdCurrent => $visitor) {
            foreach ($visitor['byRoleId'] as $roleIdCurrent => $rules) {
                unset($this->_rules['byResourceId'][$resourceIdCurrent]['byRoleId'][$roleIdCurrent]);
            }
        }

        return $this;
    }

    /**
     *
     * get all roles from the acl
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->_roles;
    }

    /**
     *
     * get the parents of a role
     *
     * @param string|\Cube\Permissions\RoleInterface $role
     *
     * @return array
     */
    public function getRoleParents($role)
    {
        $roleId = $this->getRole($role)->getId();

        return $this->_roles[$roleId]['parents'];
    }

    /**
     *
     * add resource to acl
     *
     * @param \Cube\Permissions\ResourceInterface $resource
     * @param mixed                               $parent
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addResource(ResourceInterface $resource, $parent = null)
    {
        $resourceId = $resource->getId();

        if ($this->hasResource($resource)) {
            throw new \InvalidArgumentException(
                sprintf("The resource with the id '%s' already exists.", $resourceId));
        }

        $resourceParent = null;

        if ($parent !== null) {
            if ($parent instanceof ResourceInterface) {
                $resourceParentId = $parent->getId();
            }
            else {
                $resourceParentId = $parent;
            }

            $resourceParent = $this->getResource($resourceParentId);

            $this->_resources[$resourceParentId]['children'][$resourceId] = $resource;
        }

        $this->_resources[$resourceId] = array(
            'instance' => $resource,
            'parent'   => $resourceParent,
            'children' => array()
        );

        return $this;
    }

    /**
     *
     * check if a resource exists in the acl
     *
     * @param string|\Cube\Permissions\ResourceInterface $resource
     *
     * @return bool
     */
    public function hasResource($resource)
    {
        if ($resource instanceof ResourceInterface) {
            $resourceId = $resource->getId();
        }
        else {
            $resourceId = (string)$resource;
        }

        return isset($this->_resources[$resourceId]);
    }

    /**
     *
     * get a resource instance
     *
     * @param string|\Cube\Permissions\ResourceInterface $resource
     *
     * @return \Cube\Permissions\ResourceInterface
     * @throws \InvalidArgumentException
     */
    public function getResource($resource)
    {
        if ($resource instanceof ResourceInterface) {
            $resourceId = $resource->getId();
        }
        else {
            $resourceId = (string)$resource;
        }

        if ($this->hasResource($resource) === false) {
            throw new \InvalidArgumentException(
                sprintf("The resource '%s' was not found.", $resourceId));
        }

        return $this->_resources[$resourceId]['instance'];
    }

    /**
     *
     * remove a resource from the resources array, including parent and child dependencies
     *
     * @param string|\Cube\Permissions\ResourceInterface $resource
     *
     * @return $this
     */
    public function removeResource($resource)
    {
        $resourceId = $this->getResource($resource)->getId();

        $resourcesRemoved = array($resourceId);
        if (null !== ($resourceParent = $this->_resources[$resourceId]['parent'])) {
            unset($this->_resources[$resourceParent->getResourceId()]['children'][$resourceId]);
        }
        foreach ($this->_resources[$resourceId]['children'] as $childId => $child) {
            $this->removeResource($childId);
            $resourcesRemoved[] = $childId;
        }

        unset($this->_roles[$resourceId]);
        unset($this->_resources[$resourceId]);

        return $this;
    }

    /**
     *
     * reset resources array
     *
     * @return $this
     */
    public function removeAllResources()
    {
        $this->_rules = array();
        $this->_resources = array();

        return $this;
    }

    /**
     *
     * set acl rule
     *
     * @param string                                           $operation
     * @param string                                           $type
     * @param string|array|\Cube\Permissions\RoleInterface     $roles
     * @param string|array|\Cube\Permissions\ResourceInterface $resources
     * @param string|array                                     $privileges
     * @param \Cube\Permissions\AssertInterface                $assert
     *
     * @return $this
     */
    protected function _setRule($operation, $type, $roles, $resources, $privileges = null, AssertInterface $assert = null)
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }

        $rolesTmp = $roles;
        $roles = array();

        foreach ($rolesTmp as $role) {
            $roles[] = $this->getRole($role)->getId();
        }
        unset($rolesTmp);

        if (!is_array($resources)) {
            $resources = array($resources);
        }

        $resourcesTmp = $resources;
        $resources = array();

        foreach ($resourcesTmp as $resource) {
            $resources[] = $this->getResource($resource)->getId();
        }

        if (!is_array($privileges)) {
            $privileges = array($privileges);
        }

        switch ($operation) {
            case self::OPERATION_ADD:
                foreach ($resources as $resource) {
                    foreach ($roles as $role) {
                        foreach ($privileges as $privilege) {
                            $this->_rules[$resource][$role][(string)$privilege] = array(
                                'type'   => $type,
                                'assert' => $assert,
                            );
                        }
                    }
                }
                break;

            case self::OPERATION_REMOVE:
                foreach ($resources as $resource) {
                    foreach ($roles as $role) {
                        if ($privileges !== null) {
                            foreach ($privileges as $privilege) {
                                unset($this->_rules[$resource][$role][(string)$privilege]);
                            }
                        }
                        else {
                            unset($this->_rules[$resource][$role]);
                        }

                        if (empty($this->_rules[$resource][$role])) {
                            unset($this->_rules[$resource][$role]);
                        }
                    }

                    if (empty($this->_rules[$resource])) {
                        unset($this->_rules[$resource]);
                    }
                }
                break;
        }

        return $this;
    }

    /**
     *
     * set a rule of type allow
     *
     * @param string|array|\Cube\Permissions\RoleInterface     $roles
     * @param string|array|\Cube\Permissions\ResourceInterface $resources
     * @param string|array                                     $privileges
     * @param \Cube\Permissions\AssertInterface                $assert
     *
     * @return $this
     */
    public function allow($roles = null, $resources = null, $privileges = null, AssertInterface $assert = null)
    {
        return $this->_setRule(self::OPERATION_ADD, self::TYPE_ALLOW, $roles, $resources, $privileges, $assert);
    }

    /**
     *
     * set a rule of type deny
     *
     * @param string|array|\Cube\Permissions\RoleInterface     $roles
     * @param string|array|\Cube\Permissions\ResourceInterface $resources
     * @param string|array                                     $privileges
     * @param \Cube\Permissions\AssertInterface                $assert
     *
     * @return $this
     */
    public function deny($roles = null, $resources = null, $privileges = null, AssertInterface $assert = null)
    {
        return $this->_setRule(self::OPERATION_ADD, self::TYPE_DENY, $roles, $resources, $privileges, $assert);
    }

    /**
     *
     * remove a rule of type allow
     *
     * @param string|array|\Cube\Permissions\RoleInterface     $roles
     * @param string|array|\Cube\Permissions\ResourceInterface $resources
     * @param string|array                                     $privileges
     * @param \Cube\Permissions\AssertInterface                $assert
     *
     * @return $this
     */
    public function removeAllow($roles = null, $resources = null, $privileges = null, AssertInterface $assert = null)
    {
        return $this->_setRule(self::OPERATION_REMOVE, self::TYPE_ALLOW, $roles, $resources, $privileges, $assert);
    }

    /**
     *
     * remove a rule of type deny
     *
     * @param string|array|\Cube\Permissions\RoleInterface     $roles
     * @param string|array|\Cube\Permissions\ResourceInterface $resources
     * @param string|array                                     $privileges
     * @param \Cube\Permissions\AssertInterface                $assert
     *
     * @return $this
     */
    public function removeDeny($roles = null, $resources = null, $privileges = null, AssertInterface $assert = null)
    {
        return $this->_setRule(self::OPERATION_REMOVE, self::TYPE_DENY, $roles, $resources, $privileges, $assert);
    }

    /**
     *
     * check if one or more roles are allowed for a certain resource
     * for allowed to be true, at least one role needs to be allowed
     *
     * @param array|string|\Cube\Permissions\RoleInterface $roles
     * @param string|\Cube\Permissions\ResourceInterface   $resource
     * @param string                                       $privilege
     *
     * @return bool
     */
    public function isAllowed($roles, $resource, $privilege = null)
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }

        $allowed = false;

        foreach ($roles as $role) {
            $allowed = ($this->_isAllowed($role, $resource, $privilege) === true) ? true : $allowed;
        }

        return $allowed;
    }

    /**
     *
     * check if a role is allowed to access a certain resource
     *
     * if the rule is defined by a parent, then if one parent allows the resource,
     * then return true
     * only return false if either specified by a rule of the role or if all parents
     * assert to false
     *
     * TODO: CUSTOM ASSERTIONS
     * RULE WITH NO PARENTS AND NO ACTION DOESNT SEEM TO WORK
     *
     * @param array|string|\Cube\Permissions\RoleInterface $role
     * @param string|\Cube\Permissions\ResourceInterface   $resource
     * @param string                                       $privilege
     *
     * @return bool
     */
    protected function _isAllowed($role, $resource, $privilege = null)
    {
        $roleId = $this->getRole($role)->getId();
        $resourceId = $this->getResource($resource)->getId();
        $privilege = (string)$privilege;

        $rule = null;
        $allowed = false;

        if (isset($this->_rules[$resourceId][$roleId][$privilege])) {
            // applies to a single action
            $rule = $this->_rules[$resourceId][$roleId][$privilege];
        }
        else if (isset($this->_rules[$resourceId][$roleId][''])) {
            // the rule applies to all actions
            $rule = $this->_rules[$resourceId][$roleId][''];
        }

        if ($rule !== null) {
            if ($rule['assert'] !== null) {
                $allowed = $rule['assert']->assert($this, $role, $resource, $privilege);
            }
            else if ($rule['type'] === self::TYPE_ALLOW) {
                $allowed = true;
            }
            else if ($rule['type'] === self::TYPE_DENY) {
                $allowed = false;
            }
        }
        else {
            $parents = $this->getRoleParents($role);

            foreach ($parents as $parent) {
                if ($this->isAllowed($parent, $resource, $privilege) === true) {
                    return true;
                }
            }
        }

        return $allowed;
    }

}

