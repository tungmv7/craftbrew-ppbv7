<?php

/**
 *
 * PHP Pro Bid $Id$ Rc+NPyReMnMTMn2M6FlSYgcGLlguaiY4n85VJMMvj4w=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * listing exists check controller plugin class
 * the plugin will be called when trying to view or to purchase an item. If
 * the item is not active, an error page will be displayed.
 *
 * the owner of the listing can view the listing even if it is suspended.
 */

namespace Home\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
        Ppb\Service;

class ListingExistsCheck extends AbstractPlugin
{

    public function preDispatch()
    {
        $request = $this->getRequest();

        $controller = $request->getController();
        $action = $request->getAction();


        if (
                ($controller == 'Purchase') ||
                ($controller == 'Listing' && !in_array($action, array('Create', 'CalculatePostage')))
        ) {
            $listingsService = new Service\Listings();
            $listing = $listingsService->findBy('id', $request->getParam('id'));

            if ($listing === null || !$listing->exists()) {
                $controller = 'error';
                $action = 'no-listing';

                $request->setController($controller)
                        ->setAction($action);
            }
        }
    }

}

