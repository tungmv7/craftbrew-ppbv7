<?php

/**
 *
 * PHP Pro Bid $Id$ 339kRt+P3V5JD8K1NR077H4zezovQin6nPZ9xuEEMJk=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * rss feed controller
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Ppb\Service,
    Cube\Controller\Front,
    Cube\Feed,
    Cube\Controller\Request,
    Cube\View;

class Rss extends AbstractAction
{
    protected $_feeds = array(
        'homepage' => 'Home Page Featured',
        'recent'   => 'Recently Listed',
        'ending'   => 'Ending Soon',
        'popular'  => 'Popular Listings',
    );

    public function Index()
    {
        return array(
            'headline' => $this->_('RSS Feeds'),
            'feeds'    => $this->_feeds,
        );
    }

    public function Feed()
    {
        $type = $this->getRequest()->getParam('type');

        if (!array_key_exists($type, $this->_feeds)) {
            $this->_helper->redirector()->notFound();
        }

        $mainView = Front::getInstance()->getBootstrap()->getResource('view');

        $view = new View();

        $rss = new Feed\Rss();

        $rss->setChannels(array(
            'title'       => $this->_settings['sitename'] . ' :: ' . $this->_feeds[$type],
            'link'        => $this->_settings['site_path'],
            'description' => $this->_settings['meta_description'],
            'image'       => array(
                'url'   => $this->_settings['site_path'] . '/'
                    . \Ppb\Utility::getFolder('uploads') . '/'
                    . $this->_settings['site_logo_path'],
                'title' => $this->_settings['sitename'],
                'link'  => $this->_settings['site_path'],
            ),
        ));

        $request = new Request();
        $request->setParam('type', $type);

        $listingsService = new Service\Listings();
        $listings = $listingsService->fetchAll(
            $listingsService->select(Service\Listings::SELECT_LISTINGS, $request)->limit(20)
        );

        $categoriesService = new Service\Table\Relational\Categories();

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($listings as $listing) {
            $link = $this->_settings['site_path'] . $mainView->url($listing->link(), null, false, null, false);

            $category = implode(' :: ', $categoriesService->getBreadcrumbs($listing['category_id']));

            $entry = new Feed\Entry();
            $entry->setElements(array(
                'title'       => $listing['name'],
                'description' => $listing->shortDescription(500),
                'link'        => $link,
                'guid'        => $link,
                'category'    => $category,
                'pubDate'     => date(DATE_RFC2822, strtotime($listing['start_time'])),

            ));
            $rss->addEntry($entry);
        }

        $view->setContent($rss->generateFeed());

        return $view;
    }
}

