<?php

/**
 *
 * PHP Pro Bid $Id$ jXhbm0qIsdZYx5rlAzswoHnwXC4H7F2cFKqVvAPpwMM=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * dynamic routes generator controller plugin class
 */

namespace App\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Router\Route,
    Cube\Controller\Front,
    Cube\Controller\Request,
    Ppb\Service;

class DynamicRoutes extends AbstractPlugin
{
    /**
     *
     * content sections table service
     *
     * @var \Ppb\Service\Table\Relational\ContentSections
     */
    protected $_sections;

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
     * @param array $settings settings array
     */
    public function __construct($settings)
    {
        $this->_settings = $settings;
    }

    /**
     *
     * set content sections table service
     *
     * @param \Ppb\Service\Table\Relational\ContentSections $sections
     *
     * @return $this
     */
    public function setSections(Service\Table\Relational\ContentSections $sections)
    {
        $this->_sections = $sections;

        return $this;
    }

    /**
     *
     * get content sections table service
     *
     * @return \Ppb\Service\Table\Relational\ContentSections
     */
    public function getSections()
    {
        if (!$this->_sections instanceof Service\Table\Relational\ContentSections) {
            $this->setSections(
                new Service\Table\Relational\ContentSections());
        }

        return $this->_sections;
    }


    /**
     * initialize dynamic routes
     */
    public function preRoute()
    {
        $router = Front::getInstance()->getRouter();

        $sections = $this->getSections()->fetchAll(
            $this->getSections()->getTable()->select()
                ->where('slug != ?', '')
        );

        /** @var \Ppb\Db\Table\Row\ContentSection $section */
        foreach ($sections as $section) {
            $path = $section['slug'];
            $defaults = array(
                'controller' => 'sections',
                'action'     => 'view',
                'id'         => $section['id'],
            );

            $conditions = array();

            if ($this->_settings['mod_rewrite_urls']) {
                $route = new Route\Rewrite($path, $defaults, $conditions);
            }
            else {
                $route = new Route\Standard($path, $defaults, $conditions);
            }

            $route->setName('app-section-' . $section['id'])
                ->setModule('app');

            $router->addRoute($route);
        }

        if ($this->_settings['mod_rewrite_urls']) {
//            $request = new Request\Rewrite();
        }
        else {
//            $request = new Request\Standard();
        }
//        $router->setRequest($request);
    }

}

