<?php

/**
 *
 * PHP Pro Bid $Id$ WAIHA0TeDR9sok1inTRSMi0wox8Xz9ZinktwyPUqQw8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */
/**
 * seo link redirect controller plugin class
 * will also force redirect to the installed site path if path is different.
 *
 * (*) dynamic link redirects will only work if mod rewrite is enabled
 */

namespace App\Controller\Plugin;

use Cube\Permissions\Acl as PermissionsAcl,
    Cube\Controller\Plugin\AbstractPlugin,
    Ppb\Service;

class LinkRedirect extends AbstractPlugin
{

    /**
     *
     * acl object
     *
     * @var \Cube\Permissions\Acl
     */
    protected $_acl;

    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings;

    /**
     *
     * class constructor
     *
     * @param \Cube\Permissions\Acl $acl      the acl to use
     * @param array                 $settings settings array
     */
    public function __construct(PermissionsAcl $acl, $settings)
    {
        $this->_acl = $acl;
        $this->_settings = $settings;
    }

    public function preDispatcher()
    {
        $httpHost = rtrim(sprintf(
            "%s://%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['HTTP_HOST'],
            $_SERVER['PHP_SELF']
        ));

        $request = $this->getRequest();
        if (!stristr($httpHost, $this->_settings['site_path'])) {
            $baseUrl = $request->getBaseUrl();

            $redirectUri = $this->_settings['site_path'] . str_replace($baseUrl, '', $_SERVER['REQUEST_URI']);

            $this->getResponse()
                ->setRedirect($redirectUri, 301)
                ->sendHeaders();

            exit();
        }
        else if ($this->_settings['mod_rewrite_urls']) {
            $controller = $request->getController();

            if (!$this->_acl->hasResource($controller)) {
                $link = $this->_generateLink();

                $linkRedirectsService = new Service\Table\LinkRedirects();

                $linkRedirects = $linkRedirectsService->fetchAll();

                /** @var \Cube\Db\Table\Row $linkRedirect */
                foreach ($linkRedirects as $linkRedirect) {
                    if (preg_match('#' . $linkRedirect['old_link'] . '#', $link, $matches)) {
                        unset($matches[0]);
                        $redirectUri = $this->_settings['site_path'] . vsprintf($linkRedirect['new_link'], $matches);

                        $this->getResponse()
                            ->setRedirect($redirectUri, $linkRedirect['redirect_code'])
                            ->sendHeaders();

                        exit();
                    }
                }
            }
        }
    }

    /**
     *
     * generate link string to be checked against the link redirects table
     *
     * @return string
     */
    protected function _generateLink()
    {
        $request = $this->getRequest();

        $link = rtrim($this->_settings['site_path'], '/') . '/' . ltrim($request->getRequestUri(), '/');

        $params = $request->getParams();

        if (count($params) > 0) {
            $link .= '?';

            foreach ($params as $key => $value) {
                if (!is_array($value)) {
                    $link .= $key . '=' . $value . '&';
                }
            }

            $link = rtrim($link, '&');
        }

        return $link;
    }
}

