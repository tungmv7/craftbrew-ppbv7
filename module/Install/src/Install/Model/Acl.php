<?php

/**
 *
 * PHP Pro Bid $Id$ J1PU/OjrT/R6uqbCrYJdE0zsGuJj+RPChdSCpQluRXQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * install module acl
 */

namespace Install\Model;

use Cube\Permissions;

class Acl extends Permissions\Acl
{

    public function __construct()
    {
        $guest = new Permissions\Role('Guest');
        $admin = new Permissions\Role('Admin');

        $this->addRole($guest);
        $this->addRole($admin, $guest);

        $index = new Permissions\Resource('Index');

        $this->addResource($index);

        $this->allow('Guest', 'Index');
        $this->deny('Guest', 'Index', 'Mods');
        $this->deny('Guest', 'Index', 'Logout');

        $this->deny('Admin', 'Index', 'Index');
        $this->deny('Admin', 'Index', 'Login');
        $this->allow('Admin', 'Index', 'Logout');
        $this->allow('Admin', 'Index', 'Mods');
    }

}

