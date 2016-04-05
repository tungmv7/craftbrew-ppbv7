<?php

/**
 *
 * PHP Pro Bid $Id$ Un6hmwDnsnbBvyhRZ0i6VPFLz63xxhMiLFS78r0k+L8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * language selector widget view helper class
 * - the url will never change, just that we will add a param (lang = code) which will
 *   then trigger the language change plugin
 */

namespace Ppb\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Cube\Controller\Front;

class Language extends AbstractHelper
{

    /**
     *
     * create and output language selector widget
     *
     * @return string|null
     */
    public function language()
    {
        $output = null;

        $frontController = Front::getInstance();
        $settings = $frontController->getBootstrap()->getResource('settings');

        if ($settings['user_languages']) {
            $translateOption = $frontController->getOption('translate');
            if (array_key_exists('translations', $translateOption)) {
                $languages = $translateOption['translations'];

                $view = $this->getView();

                $flagPath = $view->baseUrl . \Ppb\Utility::URI_DELIMITER . \Ppb\Utility::getFolder('img') . \Ppb\Utility::URI_DELIMITER;
                foreach ($languages as $language) {
                    $output[] = '<li>'
                        . '<a href="' . $view->url(array('lang' => $language['locale']), null, true, null, true, false) . '">'
                        . '<img src="' . $flagPath . $language['img'] . '"  alt="' . $language['desc'] . '">'
                        . '</a>'
                        . '</li>';
                }

                if (count($output) > 0) {
                    $output = '<ul class="lang-selector">' . implode(' ', $output) . '</ul>';
                }
            }
        }

        return $output;
    }

}

