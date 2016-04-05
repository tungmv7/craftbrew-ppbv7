<?php

/**
 * Created by PhpStorm.
 * User: tungmangvien
 * Date: 4/1/16
 * Time: 4:11 AM
 */
namespace Home\Controller;

use Cube\Db\Select;
use Ppb\Controller\Action\AbstractAction,
    Ppb\Service,
    Cube\Controller\Front,
    Cube\Feed,
    Cube\Controller\Request,
    Cube\View;

class Home extends AbstractAction
{
    public function Index() {
        return [];
    }

    public function Statistic()
    {
        $user = new Service\Users();
        $listing = new Service\Listings();
        $seller = $user->fetchAll(
            $user->getTable()->select()->where('is_seller = ?', 1)->where('role = ?', "User")
        )->count();
        $buyer = $user->fetchAll(
            $user->getTable()->select()->where('is_seller = ?', 0)->where('role = ?', "User")
        )->count();
        $approvedListing = $listing->fetchAll(
            $listing->getTable()->select()->where('approved = ?', 1)
        )->count();

        return [
            'statistics' => [
                ['sorts-of-beer', 'sorts of beer', 0, 'We are always brewing up something new, with a rotating selection of seasonals, nitros, and casks for you to try.'],
                ['products-every-day', 'products', $approvedListing, 'Always fresh, always rotating, these beers are hand-pulled into your glass at "cellar temperature" (55º), allowing for maximum flavor and aroma to pop out.'],
                ['shops', 'shops', $seller, 'Our brewing and packaging teams include people who speak English, Arabic, Urdu, Spanish, Mandingo and French.'],
                ['users', 'users', $buyer, 'Our technical director owns the upstate farm she grew up on, producing maple syrup and growing crops and the hops used in our Greenmarket.']
            ]
        ];
    }
}