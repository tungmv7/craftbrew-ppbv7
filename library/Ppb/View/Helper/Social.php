<?php

/**
 *
 * PHP Pro Bid $Id$ T3Q0q9pb2skSoHD+lRkhXNNa5OYmiXq4IItHz36/SdQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * social network links display view helper class
 * displays links for a certain listing if the listing is specified, or general site links otherwise
 */


namespace Ppb\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Cube\Controller\Front,
    Ppb\Db\Table\Row\Listing as ListingModel;

class Social extends AbstractHelper
{

    protected $_networks = array(
        'Email'      => array(
            'img'    => '/social/email.png',
            'link'   => '[EMAIL_FRIEND_URL]',
            'target' => '_self',
        ),
        'Facebook'   => array(
            'img'  => '/social/facebook.png',
            'link' => 'http://www.facebook.com/sharer.php?u=[URL]',
        ),
        'Twitter'    => array(
            'img'  => '/social/twitter.png',
            'link' => 'http://twitter.com/intent/tweet?text=[TEXT]&amp;url=[URL]',
        ),
        'GooglePlus' => array(
            'img'  => '/social/googleplus.png',
            'link' => 'https://plus.google.com/share?url=[URL]',
        ),
        'Pinterest'  => array(
            'img'  => '/social/pinterest.png',
            'link' => 'http://pinterest.com/pin/create/button/?url=[URL]&amp;media=[IMG]&amp;description=[TEXT]',
        ),
        'RSS'        => array(
            'img'    => '/social/rss.png',
            'link'   => '[RSS_URL]',
            'target' => '_self',
        ),
    );

    /**
     *
     * listing model
     *
     * @var \Ppb\Db\Table\Row\Listing
     */
    protected $_listing;

    /**
     *
     * set social networks array
     *
     * @param array $networks
     *
     * @return $this
     */
    public function setNetworks(array $networks)
    {
        $this->_networks = $networks;

        return $this;
    }

    /**
     *
     * get social networks array
     *
     * @return array
     */
    public function getNetworks()
    {
        return $this->_networks;
    }

    /**
     *
     * add a network to the array
     *
     * @param string $name
     * @param array  $network
     *
     * @return $this
     */
    public function addNetwork($name, $network)
    {
        $this->_networks[$name] = $network;

        return $this;
    }

    /**
     *
     * remove a network from the networks array
     *
     * @param string $name
     *
     * @return $this
     */
    public function removeNetwork($name)
    {
        if (array_key_exists($name, $this->_networks)) {
            unset($this->_networks[$name]);
        }

        return $this;
    }

    /**
     *
     * get listing model
     *
     * @return \Ppb\Db\Table\Row\Listing
     * @throws \InvalidArgumentException
     */
    public function getListing()
    {
        return $this->_listing;
    }

    /**
     *
     * set listing model
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setListing(ListingModel $listing)
    {
        if (!$listing instanceof ListingModel) {
            throw new \InvalidArgumentException("The advert model must be an instance of \Ppb\Db\Table\Row\Listing");
        }

        $this->_listing = $listing;

        return $this;
    }

    /**
     *
     * clear listing object
     *
     * @return $this
     */
    public function clearListing()
    {
        $this->_listing = null;

        return $this;
    }

    /**
     *
     * display social network links for the selected link or listing
     *
     * return string
     */
    public function display()
    {
        $links = $this->getLinks();

        $output = array();

        foreach ($links as $link) {
            $output[] = '<div class="social-button">
                    <a href="' . $link['href'] . '" target="' . $link['target'] . '" rel="nofollow"><img src="' . $link['img'] . '" alt="' . $link['name'] . '"></a>
                </div>';
        }

        return implode(' ', $output);
    }

    /**
     *
     * generate social links
     *
     * @return array
     */
    public function getLinks()
    {
        $listing = $this->getListing();

        $view = $this->getView();

        $settings = Front::getInstance()->getBootstrap()->getResource('settings');

        $sitePath = $settings['site_path'];
        $imgBaseUrl = $view->baseUrl . \Ppb\Utility::URI_DELIMITER . \Ppb\Utility::getFolder('img');
        $uploadsPath = $sitePath . \Ppb\Utility::URI_DELIMITER . \Ppb\Utility::getFolder('uploads');
        $output = array();

        if ($listing instanceof ListingModel) {
            $url = urlencode($sitePath . $this->getView()->url($listing->link(), null, false, null, false));
            $text = urlencode($listing->getData('name'));
            $img = urlencode($listing->getMainImage(true));
            $desc = urlencode(substr(strip_tags($listing->getData('description')), 0, 150));
            $emailFriendUrl = $view->url(array('module' => 'listings', 'controller' => 'listing', 'action' => 'email-friend', 'id' => $listing->getData('id')));
            $rssUrl = null;
        }
        else {
            $url = urlencode($sitePath);
            $text = urlencode($settings['sitename']);
            $img = urlencode($uploadsPath . \Ppb\Utility::URI_DELIMITER . $settings['site_logo_path']);
            $desc = urlencode($settings['meta_description']);
            $emailFriendUrl = null;
            $rssUrl = $view->url(array('module' => 'app', 'controller' => 'rss', 'action' => 'index'));
        }

        foreach ($this->_networks as $name => $network) {
            $href = str_replace(
                array('[URL]', '[TEXT]', '[IMG]', '[DESC]', '[RSS_URL]', '[EMAIL_FRIEND_URL]'),
                array($url, $text, $img, $desc, $rssUrl, $emailFriendUrl),
                $network['link']);

            $target = (isset($network['target'])) ? $network['target'] : '_blank';

            if ($listing && $name == 'RSS') {
            }
            else if (!$listing && $name == 'Email') {
            }
            else {
                $output[] = array(
                    'name'   => $name,
                    'href'   => $href,
                    'target' => $target,
                    'img'    => $imgBaseUrl . $network['img'],
                );
            }
        }

        return $output;
    }

    /**
     *
     * main method, only returns object instance
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     *
     * @return $this
     */
    public function social(ListingModel $listing = null)
    {
        if ($listing !== null) {
            $this->setListing($listing);
        }

        return $this;
    }

}

