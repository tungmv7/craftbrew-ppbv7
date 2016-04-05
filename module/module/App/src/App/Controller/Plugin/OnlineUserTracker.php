<?php

/**
 *
 * PHP Pro Bid $Id$ YYNedEtG1uz5ACysMfob1kJfBpvF8/7cHXenfSo031Q=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.1
 */
/**
 * online user activity tracker controller plugin class
 */

namespace App\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Front,
    Ppb\Service;

class OnlineUserTracker extends AbstractPlugin
{
    /**
     *
     * users statistics table service
     *
     * @var \Ppb\Service\UsersStatistics
     */
    protected $_usersStatistics;

    /**
     *
     * set users statistics table service
     *
     * @param \Ppb\Service\UsersStatistics $usersStatistics
     *
     * @return $this
     */
    public function setUsersStatistics(Service\UsersStatistics $usersStatistics)
    {
        $this->_usersStatistics = $usersStatistics;

        return $this;
    }

    /**
     *
     * get users statistics table service
     *
     * @return \Ppb\Service\UsersStatistics
     */
    public function getUsersStatistics()
    {
        if (!$this->_usersStatistics instanceof Service\UsersStatistics) {
            $this->setUsersStatistics(
                new Service\UsersStatistics());
        }

        return $this->_usersStatistics;
    }


    /**
     * save user activity
     */
    public function postDispatch()
    {
        $request = $this->getRequest();
        $module = $request->getModule();

        if ($module != 'Admin') {
            $bootstrap = Front::getInstance()->getBootstrap();
            $user = $bootstrap->getResource('user');
            $view = $bootstrap->getResource('view');

            $data = array(
                'remote_addr'          => (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '',
                'request_uri'          => (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '',
                'page_title'           => strip_tags($view->headTitle()),
                'http_user_agent'      => (!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '',
                'http_accept_language' => (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '',
                'http_referrer'        => (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '',
            );

            if (!empty($user['id'])) {
                $data['user_id'] = $user['id'];
            }

            if ($this->_crawlerDetect($data['http_user_agent']) === false) {
                $this->getUsersStatistics()->save($data);
            }
        }

        return $this;
    }

    /**
     *
     * do not consider crawlers as online users
     *
     * @param $USER_AGENT
     *
     * @return bool
     */
    protected function _crawlerDetect($USER_AGENT)
    {
        $crawlers = array(
            array('Google', 'Google'),
            array('msnbot', 'MSN'),
            array('Rambler', 'Rambler'),
            array('Yahoo', 'Yahoo'),
            array('AbachoBOT', 'AbachoBOT'),
            array('accoona', 'Accoona'),
            array('AcoiRobot', 'AcoiRobot'),
            array('ASPSeek', 'ASPSeek'),
            array('CrocCrawler', 'CrocCrawler'),
            array('Dumbot', 'Dumbot'),
            array('FAST-WebCrawler', 'FAST-WebCrawler'),
            array('GeonaBot', 'GeonaBot'),
            array('Gigabot', 'Gigabot'),
            array('Lycos', 'Lycos spider'),
            array('MSRBOT', 'MSRBOT'),
            array('Scooter', 'Altavista robot'),
            array('AltaVista', 'Altavista robot'),
            array('IDBot', 'ID-Search Bot'),
            array('eStyle', 'eStyle Bot'),
            array('Synapse', 'Synapse Bot'),
            array('Baiduspider', 'Baiduspider'),
            array('YandexBot', 'YandexBot'),
            array('MJ12bot', 'MJ12bot'),
            array('Scrubby', 'Scrubby robot')
        );

        foreach ($crawlers as $c) {
            if (stristr($USER_AGENT, $c[0])) {
                return ($c[1]);
            }
        }

        return false;
    }

}

