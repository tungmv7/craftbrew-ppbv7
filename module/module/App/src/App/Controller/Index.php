<?php

/**
 *
 * PHP Pro Bid $Id$ 5tXTQRUmBmY8Yv2BtEI+Quf8X4snXqO4F/EWyUb/NAM=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * index controller
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Cube\View,
    Cube\Validate\Url as UrlValidator,
    Cube\Controller\Request,
    Ppb\Service;

class Index extends AbstractAction
{

    /**
     *
     * this action doesn't do anything, all content is generated in the view helper
     * in order for it to be theme specific
     *
     * @return array
     */
    public function Index()
    {
        return array(
            'indexPage' => true,
        );
    }

    /**
     *
     * this action will count the click for an advert and redirect to the advert's url
     */
    public function AdvertRedirect()
    {
        $id = $this->getRequest()->getParam('id');

        $advertsService = new Service\Advertising();

        /** @var \Ppb\Db\Table\Row\Advert $advert */
        $advert = $advertsService->findBy('id', $id);

        if (count($advert) > 0) {
            $advert->addClick();
            $this->_helper->redirector()->gotoUrl($advert['url']);
        }
        else {
            $this->_helper->redirector()->notFound();
        }
    }

    public function PlayVideo()
    {
        $id = $this->getRequest()->getParam('id');
        $listingsMediaService = new Service\ListingsMedia();

        /** @var \Ppb\Db\Table\Row\ListingMedia $video */
        $video = $listingsMediaService->findBy('id', $id);

        $this->_setNoLayout();

        return array(
            'video' => $video,
        );
    }

    public function Sitemap()
    {
        $this->getResponse()->setHeader('Content-Type: text/xml; charset=utf-8');

        /** @var \Ppb\View\Helper\Url $urlHelper */
        $urlHelper = Front::getInstance()->getBootstrap()->getResource('view')->getHelper('url');

        $view = new View();
        $pages = array();

        $pages[] = array(
            'loc'        => $urlHelper->url(null, 'app-home'),
            'changefreq' => 'daily',
            'priority'   => '1.0'
        );

        $categoriesService = new Service\Table\Relational\Categories();
        $categories = $categoriesService->fetchAll(
            $categoriesService->getTable()
                ->select(array('id', 'name', 'slug'))
                ->where('parent_id is null')
                ->where('enable_auctions = ?', 1)
                ->where('user_id IS NULL')
        );

        /** @var \Ppb\Db\Table\Row\Category $category */
        foreach ($categories as $category) {
            $pages[] = array(
                'loc'        => $urlHelper->url($category->link()),
                'changefreq' => 'daily',
                'priority'   => '0.5',
            );
        }

        $request = new Request();
        $request->clearParams();

        $listingsService = new Service\Listings();
        $listings = $listingsService->fetchAll(
            $listingsService->select(Service\Listings::SELECT_LISTINGS), $request);

        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($listings as $listing) {
            $pages[] = array(
                'loc'        => $urlHelper->url($listing->link()),
                'changefreq' => 'daily',
                'priority'   => '1.0',
            );
        }

        $contentSectionsService = new Service\Table\Relational\ContentSections();
        $contentSections = $contentSectionsService->fetchAll();

        $urlValidator = new UrlValidator();
        foreach ($contentSections as $contentSection) {
            $urlValidator->setValue($contentSection['slug']);
            if (!$urlValidator->isValid()) {
                $pages[] = array(
                    'loc'        => $urlHelper->url($contentSection['slug']),
                    'changefreq' => 'weekly',
                    'priority'   => '0.4'
                );
            }
        }

        $content = '<?xml version="1.0" encoding="UTF-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($pages as $page) {
            $content .= '<url>
		        <loc>' . htmlentities($page['loc']) . '</loc>
		        <changefreq>' . $page['changefreq'] . '</changefreq>
		        <priority>' . $page['priority'] . '</priority>
	        </url>';
        }

        $content .= '</urlset>';

        $view->setContent($content);

        return $view;
    }

    public function MaintenanceMode()
    {
        $this->_setNoLayout();

        return array();
    }
}

