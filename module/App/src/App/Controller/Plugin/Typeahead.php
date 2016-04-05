<?php

/**
 *
 * PHP Pro Bid $Id$ BzVsfNPEQgB9lPVjYUDur1GneWIdjYzHr4tXuEllfD8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * TYPEAHEAD MOD
 */
/**
 * this plugin will initialize the typeahead module
 * and will populate the tags table
 */

namespace App\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Front,
    Ppb\Service,
    Cube\Db\Expr;

class Typeahead extends AbstractPlugin
{

    /**
     * min search string length
     */
    const STRLEN_MIN = 3;

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;


    public function __construct()
    {
        $this->_view = Front::getInstance()->getBootstrap()->getResource('view');
    }

    /**
     *
     * we get the view and initialize the css and js for the plugin
     */
    public function preDispatch()
    {
        $request = $this->getRequest();

        $module = $request->getModule();

        if ($module !== 'Admin') {
            $baseUrl = $request->getBaseUrl();

            /** @var \Cube\View\Helper\Script $scriptHelper */
            $scriptHelper = $this->_view->getHelper('script');
            $scriptHelper->addHeaderCode(
                '<link href="' . $baseUrl . '/js/typeahead/autocomplete.css" media="all" rel="stylesheet" type="text/css">')
                ->addBodyCode('<script type="text/javascript" src="' . $baseUrl . '/js/typeahead/autocomplete.js"></script>');
        }
    }

    /**
     *
     * after we have dispatched the request,
     * we check if the search results action has been called, and if true, we save the search tags (keywords/category)
     * in the autocomplete tags table, but only if there were results that matched.
     */
    public function postDispatcher()
    {
        $request = $this->getRequest();

        $module     = $request->getModule();
        $controller = $request->getController();
        $action     = $request->getAction();

        if ($module == 'Listings' && $controller == 'Browse' && $action == 'Index') {
            $nbItems = $this->_view->get('paginator')->getPages()->totalItemCount;

            // save tags
            $keywords   = $request->getParam('keywords');
            $categoryId = intval($request->getParam('parent_id'));

            if (!empty($keywords) && strlen($keywords) >= self::STRLEN_MIN) {
                $tagsService = new Service\AutocompleteTags();

                $select = $tagsService->getTable()->select()
                    ->where('keywords = ?', $keywords);

                if ($categoryId) {
                    $select->where('category_id = ?', $categoryId);
                } else {
                    $select->where('category_id IS NULL');
                }

                $result = $tagsService->getTable()->fetchRow($select);

                if (count($result) > 0) {
                    $result->save(array(
                        'nb_hits'    => $result['nb_hits'] + 1,
                        'nb_results' => $nbItems,
                        'updated_at' => new Expr('now()'),
                    ));
                } else if ($nbItems > 0) {
                    $tagsService->save(array(
                        'keywords'    => $keywords,
                        'category_id' => ($categoryId) ? $categoryId : new Expr('null'),
                        'nb_hits'     => 1,
                        'nb_results'  => $nbItems,
                    ));
                }
            }
        }
    }
}

