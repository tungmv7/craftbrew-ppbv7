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
 * global modules acl
 */

namespace Home\Model;

use Cube\Permissions;

class Acl extends Permissions\Acl
{

    public function __construct()
    {
        $guest = new Permissions\Role('Guest');
        $incomplete = new Permissions\Role('Incomplete');
        $suspended = new Permissions\Role('Suspended');
        $user = new Permissions\Role('User');
        $buyer = new Permissions\Role('Buyer');
        $seller = new Permissions\Role('Seller');
        $buyerSeller = new Permissions\Role('BuyerSeller');

        $this->addRole($guest);
        $this->addRole($incomplete, $guest);
        $this->addRole($suspended, $guest);
        $this->addRole($user, $guest);
        $this->addRole($buyer, $user);
        $this->addRole($seller, $user);
        $this->addRole($buyerSeller, array($buyer, $seller));

        $index = new Permissions\Resource('Index');
        $sections = new Permissions\Resource('Sections');
        $uploader = new Permissions\Resource('Uploader');
        $async = new Permissions\Resource('Async');
        $payment = new Permissions\Resource('Payment');
        $cron = new Permissions\Resource('Cron');
        $typeahead = new Permissions\Resource('Typeahead');
        $rss = new Permissions\Resource('Rss');

        $this->addResource($index);
        $this->addResource($sections);
        $this->addResource($uploader);
        $this->addResource($async);
        $this->addResource($payment);
        $this->addResource($cron);
        $this->addResource($typeahead);
        $this->addResource($rss);

        $this->allow('Guest', 'Index');
        $this->allow('Guest', 'Sections');

        // the flash component doesnt store the session
        $this->allow('Guest', 'Uploader');

        // async controller - allowed for everyone
        $this->allow('Guest', 'Async');

        $this->allow('Guest', 'Typeahead');
        $this->allow('Guest', 'Rss');

        // payment controller - signup fee, ipn, completed and failed actions are allowed for everyone,
        // all other fees only allowed if a user is logged in
        $this->allow('Guest', 'Payment', 'UserSignup');
        $this->allow('Guest', 'Payment', 'Ipn');
        $this->allow('Guest', 'Payment', 'Completed');
        $this->allow('Guest', 'Payment', 'Failed');
        $this->allow('Suspended', 'Payment', 'CreditBalance');
        $this->allow('User', 'Payment');
        $this->deny('User', 'Payment', 'UserSignup');

        // cron jobs controller - allowed for everyone
        $this->allow('Guest', 'Cron');


        /* listings module */
        $listingResource = new Permissions\Resource('Listing');
        $this->addResource($listingResource);
        $this->allow('Seller', 'Listing');
    }

}

