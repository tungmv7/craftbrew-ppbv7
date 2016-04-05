<?php

/**
 *
 * PHP Pro Bid $Id$ J1PU/OjrT/R6uqbCrYJdE0zsGuJj+RPChdSCpQluRXQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * listings module acl
 */

namespace Listings\Model;

use Cube\Permissions,
        Cube\Controller\Front;

class Acl extends Permissions\Acl
{

    public function __construct()
    {
        $settings = Front::getInstance()->getBootstrap()->getResource('settings');

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

        $listingResource = new Permissions\Resource('Listing');
        $browseResource = new Permissions\Resource('Browse');
        $searchResource = new Permissions\Resource('Search');
        $purchaseResource = new Permissions\Resource('Purchase');
        $cartResource = new Permissions\Resource('Cart');
        $categories = new Permissions\Resource('Categories');

        $this->addResource($listingResource);
        $this->addResource($browseResource);
        $this->addResource($searchResource);
        $this->addResource($purchaseResource);

        $this->addResource($categories);

        $this->allow('Seller', 'Listing');
        $this->allow('Guest', 'Listing', 'Details');
        $this->allow('Guest', 'Listing', 'CalculatePostage');
        $this->allow('Guest', 'Listing', 'Watch');
        $this->allow('User', 'Listing', 'EmailFriend');

        $this->allow('Guest', 'Browse');
        $this->deny('Guest', 'Browse', 'FavoriteStore');
        $this->allow('User', 'Browse', 'FavoriteStore');

        $this->allow('Guest', 'Search');
        $this->allow('Buyer', 'Purchase');

        // categories controller - allowed for everyone
        $this->allow('Guest', 'Categories');

        // add and assign roles to the shopping cart resource
        if ($settings['enable_shopping_cart']) {
            $this->addResource($cartResource);
            $this->allow('Guest', 'Cart');
            $this->deny(array('Incomplete', 'Suspended'), 'Cart', 'Checkout');
        }
    }

}

