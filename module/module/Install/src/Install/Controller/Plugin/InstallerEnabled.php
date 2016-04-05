<?php

/**
 *
 * PHP Pro Bid $Id$ dJeT9vc5qzgRG09rpEX7lYhoNM80acFYlX0N6feY2to=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.1
 */
/**
 * checks if installer module is enabled from the admin area and redirects to the app index page otherwise
 */

namespace Install\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Front;

class InstallerEnabled extends AbstractPlugin
{

    public function preDispatcher()
    {
        $settings = Front::getInstance()->getBootstrap()->getResource('settings');

        if (array_key_exists('disable_installer', (array)$settings)) {
            if ($settings['disable_installer']) {
                $this->getResponse()
                    ->setRedirect($settings['site_path'], 301)
                    ->sendHeaders();

            }
        }

    }

}

