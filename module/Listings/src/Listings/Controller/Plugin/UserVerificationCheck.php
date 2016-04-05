<?php

/**
 *
 * PHP Pro Bid $Id$ DrXwT6jhJHcdDl4O1hEssb+Ii/GgrH03ygir14kDRYjXB2ptyFfCwFEsS2KSccbKU9bQUZlwwPZMv09DmUltKQ==
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * user verification check controller plugin class
 * the plugin will be called when trying to list or to purchase an item.
 * - if mandatory seller verification is enabled and the listing create/edit page is accessed,
 * the user will be redirected to the verification page
 * - if mandatory buyer verification is enabled and the purchase confirm page is accessed (or the shopping cart checkout button is clicked),
 * the user will be redirected to the verification page
 */

namespace Listings\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
        Cube\Controller\Front,
        Ppb\Service;

class UserVerificationCheck extends AbstractPlugin
{

    public function preDispatch()
    {
        $request = $this->getRequest();

        $controller = $request->getController();
        $action = $request->getAction();

        if ($controller == 'Purchase' ||
            ($controller == 'Listing' && $action == 'Create')
        ) {
            $bootstrap = Front::getInstance()->getBootstrap();
            $user = $bootstrap->getResource('user');
            $settings = $bootstrap->getResource('settings');

            if (count($user) > 0) {
                if (!$user->isVerified()) {
                    if (($controller == 'Purchase' && $settings['buyer_verification_mandatory']) ||
                        ($controller == 'Listing' && $action == 'Create' && $settings['seller_verification_mandatory'])
                    ) {
                        $module = 'Members';
                        $controller = 'user';
                        $action = 'verification';

                        $request->setModule($module)
                                ->setController($controller)
                                ->setAction($action);
                    }
                }
            }
        }
    }

}

