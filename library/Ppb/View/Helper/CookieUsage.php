<?php

/**
 *
 * PHP Pro Bid $Id$ xu6yvgz1lZgB8ns28gkVQOqWg+5hhwmM9grJahyMfts=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * cookie usage view helper class
 */

namespace Ppb\View\Helper;

use Cube\Controller\Front,
    Cube\View\Helper\AbstractHelper;

class CookieUsage extends AbstractHelper
{

    /**
     * cookie name
     */
    const COOKIE_USAGE = 'CookieUsage';

    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings;

    public function __construct(array $settings)
    {
        $this->setSettings($settings);
    }

    /**
     *
     * set settings array
     *
     * @param array $settings
     * @return $this
     */
    public function setSettings(array $settings)
    {
        $this->_settings = $settings;

        return $this;
    }

    /**
     *
     * render cookie usage confirmation message
     *
     * @return null|string
     */
    public function cookieUsage()
    {
        $output = null;

        if ($this->_settings['enable_cookie_usage_confirmation']) {
            $view = $this->getView();
            $bootstrap = Front::getInstance()->getBootstrap();
            /** @var \Cube\Session $session */
            $session = $bootstrap->getResource('session');
            if (!$session->getCookie(self::COOKIE_USAGE)) {
                /** @var \Cube\View\Helper\Script $helper */
                $helper = $view->getHelper('script');
                $cookieKey = $session->getCookieKey(self::COOKIE_USAGE);

                $cookiePath = (!empty($view->baseUrl)) ? $view->baseUrl : $view::URI_DELIMITER;

                $helper->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/cookie.js"></script>')
                    ->addBodyCode("
                        <script type=\"text/javascript\">
                            $('.btn-cookie-confirm').on('click', function() {
                                $.cookie('" . $cookieKey . "', '1', {path: '" . $cookiePath . "', expires: 30});
                                $('.cookie-usage').remove();
                            });
                        </script>");

                $translate = $this->getTranslate();

                $output = '<div class="cookie-usage">
                    <div class="row">
                        <div class="col-sm-10">' . $translate->_($this->_settings['cookie_usage_message']) . '</div>
                        <div class="col-sm-2 text-right">
                            <button class="btn btn-sm btn-primary btn-cookie-confirm">' . $translate->_('I Understand') . '</button>
                        </div>
                    </div>
                </div>';
            }
        }

        return $output;
    }


}

