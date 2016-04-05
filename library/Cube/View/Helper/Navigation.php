<?php

/**
 *
 * Cube Framework $Id$ PFL7mdv7MVBIarBqdAE/L7FJWT6n8dBWvhnPdrbXuhc=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * navigation view helper
 */

namespace Cube\View\Helper;

use Cube\Navigation\AbstractContainer,
    Cube\Navigation\Page\AbstractPage,
    Cube\Permissions;

class Navigation extends AbstractHelper
{

    /**
     *
     * initial navigation object (should not modified)
     *
     * @var \Cube\Navigation\Page\AbstractPage
     */
    protected $_initialContainer;

    /**
     *
     * navigation object
     *
     * @var \Cube\Navigation\Page\AbstractPage
     */
    protected $_container;

    /**
     *
     * path where to search for navigation view partials
     *
     * @var string
     */
    protected $_path;

    /**
     *
     * the minimum depth from which the rendering will start
     * default = 0 - from first page
     *
     * @var integer
     */
    protected $_minDepth = 0;

    /**
     *
     * the maximum depth where the rendering will stop
     * default = 0 - until last page
     *
     * @var integer
     */
    protected $_maxDepth = 0;

    /**
     *
     * ACL object to use
     *
     * @var \Cube\Permissions\Acl
     */
    protected $_acl;

    /**
     *
     * ACL role to use
     *
     * @var string|\Cube\Permissions\RoleInterface
     */
    protected $_role;

    /**
     *
     * get the initial navigation object
     *
     * @return \Cube\Navigation\Page\AbstractPage
     * @throws \InvalidArgumentException
     */
    public function getInitialContainer()
    {
        if (!$this->_initialContainer instanceof AbstractContainer) {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Navigation\AbstractContainer.", $this->_container));
        }

        return $this->_initialContainer;
    }

    /**
     *
     * set the initial navigation container
     *
     * @param \Cube\Navigation\Page\AbstractPage $container
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setInitialContainer($container)
    {
        if ($container instanceof AbstractContainer) {
            $this->_initialContainer = $container;
            $this->setContainer($this->_initialContainer);
        }
        else {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Navigation\AbstractContainer.", $this->_container));
        }

        return $this;
    }

    /**
     *
     * get the navigation object
     *
     * @return \Cube\Navigation\Page\AbstractPage
     * @throws \InvalidArgumentException
     */
    public function getContainer()
    {
        if (!$this->_container instanceof AbstractContainer) {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Navigation\AbstractContainer.", $this->_container));
        }

        return $this->_container;
    }

    /**
     *
     * set the navigation container
     *
     * @param \Cube\Navigation\Page\AbstractPage $container
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setContainer($container)
    {
        if ($container instanceof AbstractContainer) {
            $this->_container = $container;
        }
        else if ($container !== null) {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Navigation\AbstractContainer.", $this->_container));
        }

        return $this;
    }

    /**
     *
     * reset container
     *
     * @param bool $initialContainer
     *
     * @return $this
     */
    public function resetContainer($initialContainer = true)
    {
        if ($initialContainer === true) {
            $this->_container = $this->getInitialContainer();
        }
        else {
            $this->_container = null;
        }

        return $this;
    }

    /**
     *
     * get navigation partials path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     *
     * set navigation partials path
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
//        if (!is_dir($path)) {
//            throw new \InvalidArgumentException(
//                sprintf("The navigation files path (%s) does not exist.", $path));
//        }

        if (is_dir($path)) {
            $this->_path = $path;
        }

        return $this;
    }

    /**
     *
     * get the minimum depth of the container
     *
     * @return integer
     */
    public function getMinDepth()
    {
        return $this->_minDepth;
    }

    /**
     *
     * set the minimum depth of the container
     *
     * @param int $minDepth
     *
     * @return $this
     */
    public function setMinDepth($minDepth)
    {
        $this->_minDepth = (int)$minDepth;

        return $this;
    }

    /**
     *
     * get the maximum depth of the container
     *
     * @return int
     */
    public function getMaxDepth()
    {
        return $this->_maxDepth;
    }

    /**
     *
     * set the maximum depth of the container
     *
     * @param int $maxDepth
     *
     * @return $this
     */
    public function setMaxDepth($maxDepth)
    {
        $this->_maxDepth = (int)$maxDepth;

        return $this;
    }

    /**
     *
     * get ACL
     *
     * @return \Cube\Permissions\Acl
     * @throws \InvalidArgumentException
     */
    public function getAcl()
    {
        if (!$this->_acl instanceof Permissions\Acl) {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Permissions\Acl.", $this->_acl));
        }

        return $this->_acl;
    }

    /**
     *
     * set ACL
     *
     * @param \Cube\Permissions\Acl $acl
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setAcl($acl)
    {
        if ($acl instanceof Permissions\Acl) {
            $this->_acl = $acl;
        }
        else {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Permissions\Acl.", $this->_acl));
        }

        return $this;
    }

    /**
     *
     * get ACL role
     *
     * @return string|\Cube\Permissions\RoleInterface
     */
    public function getRole()
    {
        return $this->_role;
    }

    /**
     *
     * set ACL role
     *
     * @param string|\Cube\Permissions\RoleInterface $role
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setRole($role)
    {
        if ($role === null || is_string($role) || $role instanceof Permissions\RoleInterface) {
            $this->_role = $role;
        }
        else {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be null, a string, or an instance of type \Cube\Permissions\RoleInterface.",
                    $this->_role));
        }

        return $this;
    }

    /**
     *
     * function called by the reflection class when creating the helper
     * we will always check if the navigation object and view file are set correctly when calling the navigation proxy class
     *
     * @param \Cube\Navigation\AbstractContainer $container the navigation object
     * @param string                             $partial   the name of the view partial used for rendering the navigation object
     *
     * @return $this
     */
    public function navigation($container = null, $partial = null)
    {
        if ($container !== null) {
            $this->setContainer($container);
            $this->setPartial($partial);
        }

        return $this;
    }

    /**
     *
     * create a navigation menu from a navigation container and a view partial
     *
     * @return string       the rendered menu
     */
    public function menu()
    {
        $view = $this->getView();
        $view->set('menu', $this->getContainer());

        return $view->process(
            $this->getPartial(), true);
    }

    /**
     *
     * find the active page in the container set in the helper
     *
     * @return \Cube\Navigation\Page\AbstractPage|null      return the page object if found or null otherwise
     */
    public function findActive()
    {
        $container = $this->getContainer();

        if ($container->isActive()) {
            return $container;
        }
        $iterator = new \RecursiveIteratorIterator($container,
            \RecursiveIteratorIterator::CHILD_FIRST);

        /** @var \Cube\Navigation\Page\AbstractPage $page */
        foreach ($iterator as $page) {
            if ($page->isActive()) {
                return $page;
            }
        }

        return null;
    }

    /**
     *
     * checks if a page is accepted in the iteration
     * the method is to be called from the navigation view helper
     *
     * @param \Cube\Navigation\Page\AbstractPage $page
     * @param bool                               $recursive default true
     *
     * @return bool
     */
    public function accept(AbstractPage $page, $recursive = true)
    {
        $accept = true;

        if (!$this->_acceptAcl($page)) {
            $accept = false;
        }

        if ($accept && $recursive) {
            $parent = $page->getParent();
            if ($parent instanceof AbstractPage) {
                $accept = $this->accept($parent, true);
            }
        }

        return $accept;
    }

    /**
     *
     * check if a page is allowed by ACL
     *
     * rules:
     * - helper has no ACL, page is accepted
     * - page has a resource or privilege defined:
     *   => the ACL allows access to it using the helper's role,
     *   => [OBSOLETE] the ACL doesn't have the resource called in the page
     *   => if the resource isn't in the ACL - page isn't accepted
     *
     * - if page has no resource or privilege, page is accepted
     *
     * @param \Cube\Navigation\Page\AbstractPage $page
     *
     * @return bool
     */
    protected function _acceptAcl(AbstractPage $page)
    {
        if (!$acl = $this->getAcl()) {
            return true;
        }

        $role = $this->getRole();
        $resource = $page->getResource();
        $privilege = $page->getPrivilege();

        if ($resource || $privilege) {
            if ($acl->hasResource($resource)) {
                return $acl->isAllowed($role, $resource, $privilege);
            }

            return false;
        }

        return true;
    }

    /**
     *
     * get active page breadcrumbs array
     *
     * @return array
     */
    public function getBreadcrumbs()
    {
        $breadcrumbs = array();
        $depth = 0;

        $page = $this->findActive();

        if ($page instanceof AbstractPage) {

            array_push($breadcrumbs, $page);

            while (($parent = $page->getParent()) instanceof AbstractPage) {
                array_push($breadcrumbs, $parent);
                $page = $parent;
            }

            $breadcrumbs = array_reverse($breadcrumbs);

            foreach ($breadcrumbs as $key => $page) {
                if ($depth < $this->_minDepth ||
                    ($depth > $this->_maxDepth && $this->_maxDepth > 0)
                ) {
                    unset($breadcrumbs[$key]);
                }

                $depth++;
            }
        }

        return $breadcrumbs;
    }

    /**
     *
     * create a breadcrumbs helper by getting the active page from the navigation container
     * and applying it to a breadcrumbs view partial
     * if no active page is found, return an empty display output
     *
     * @return string|null
     */
    public function breadcrumbs()
    {
        $breadcrumbs = $this->getBreadcrumbs();

        if (count($breadcrumbs) > 0) {
            $view = $this->getView();
            $view->set('breadcrumbs', $breadcrumbs);

            return $view->process(
                $this->getPartial(), true);
        }

        return null;
    }
}

