<?php

/**
 *
 * Cube Framework $Id$ J1PU/OjrT/R6uqbCrYJdE0zsGuJj+RPChdSCpQluRXQ=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * admin module acl
 */

namespace Admin\Model;

use Cube\Permissions;

class Acl extends Permissions\Acl
{

    public function __construct()
    {
        $guest = new Permissions\Role('Guest');
        $manager = new Permissions\Role('Manager');
        $admin = new Permissions\Role('Admin');

        $this->addRole($guest);
        $this->addRole($manager, $guest);
        $this->addRole($admin, $manager);

        $index = new Permissions\Resource('Index');
        $settings = new Permissions\Resource('Settings');
        $users = new Permissions\Resource('Users');
        $tables = new Permissions\Resource('Tables');
        $fees = new Permissions\Resource('Fees');
        $stores = new Permissions\Resource('Stores');
        $listings = new Permissions\Resource('Listings');
        $customFields = new Permissions\Resource('CustomFields');
        $siteContent = new Permissions\Resource('SiteContent');
        $tools = new Permissions\Resource('Tools');
        $tax = new Permissions\Resource('Tax');

        $this->addResource($index);
        $this->addResource($settings);
        $this->addResource($users);
        $this->addResource($tables);
        $this->addResource($fees);
        $this->addResource($stores);
        $this->addResource($listings);
        $this->addResource($customFields);
        $this->addResource($siteContent);
        $this->addResource($tools);
        $this->addResource($tax);

        $this->allow('Guest', 'Index', 'Login');
        $this->deny('Guest', 'Index', 'Logout');

        $this->allow('Manager', 'Index');
        $this->allow('Manager', 'Index', 'Logout');
        $this->deny('Manager', 'Index', 'Login');

        $this->allow('Admin', 'Settings');
        $this->allow('Manager', 'Users');
        $this->allow('Admin', 'Tables');
        $this->allow('Admin', 'Fees');
        $this->allow('Admin', 'Stores');
        $this->allow('Manager', 'Listings');
        $this->allow('Admin', 'CustomFields');
        $this->allow('Admin', 'SiteContent');
        $this->allow('Admin', 'Tools');
        $this->allow('Admin', 'Tax');
    }

}

