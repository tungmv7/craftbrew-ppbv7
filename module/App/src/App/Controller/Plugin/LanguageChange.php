<?php

/**
 *
 * PHP Pro Bid $Id$ Ne72C0MfUEtjr83+OCFUFU9jbcAY9d+I2PweDan6PFg=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * language change controller plugin class
 */

namespace App\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Locale,
    Cube\Controller\Front;

class LanguageChange extends AbstractPlugin
{
    const LANG_TOKEN = 'LanguageToken';

    public function postRoute()
    {
        $lang = $this->getRequest()->getParam('lang');

        if ($lang) {
            $bootstrap = Front::getInstance()->getBootstrap();
            $settings = $bootstrap->getResource('settings');

            if ($settings['user_languages']) {
                if (Locale::isLocale($lang)) {
                    $session = $bootstrap->getResource('session');
                    $session->setCookie(Locale::LANG_TOKEN, $lang, $this->getRequest()->getBaseUrl());

                    $bootstrap->removeResource('language')
                        ->setResource('language', $lang);

                    $locale = $bootstrap->getResource('locale');
                    $locale->setLocale($lang);

                    $translate = $bootstrap->getResource('translate');

                    $translate->getAdapter()->setLocale($locale);
                }
            }
        }
    }

}

