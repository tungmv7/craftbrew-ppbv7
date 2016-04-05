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

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\View,
    Ppb\Service\AutocompleteTags as AutocompleteTagsService;

class Typeahead extends AbstractAction
{

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     *
     * autocomplete tags service
     *
     * @var \Ppb\Service\AutocompleteTags
     */
    protected $_tags;

    public function init()
    {
        $this->_view = new View();
        $this->_tags = new AutocompleteTagsService();
    }

    public function Prefetch()
    {
        $select = $this->_tags->getTable()->select();
        $select->order('nb_hits DESC')
            ->limit(100);

        $results = $this->_tags->fetchAll($select);

        if (count($results) > 0) {
            foreach ($results as $result) {
                $categoryName = null;

                if ($result['category_id']) {
                    $categoryName = $result->findParentRow('\Ppb\Db\Table\Categories')->name;
                }

                $tags[] = array(
                    'value'        => $result['keywords'],
                    'tokens'       => $result['keywords'],
                    'categoryId'   => $result['category_id'],
                    'categoryName' => $categoryName,
                );
            }

        }

        $this->getResponse()->setHeader('Content-Type: application/json');

        $this->_view->setContent(
            json_encode($tags));

        return $this->_view;
    }

    public function Remote()
    {
        $tags = array();

        $term = $this->getRequest()->getParam('term');

        $this->getResponse()->setHeader('Content-Type: application/json');

        if (!empty($term)) {
            $keywords = '%' . str_replace(' ', '%', $term) . '%';

            $select = $this->_tags->getTable()->select();
            $select->where('keywords LIKE ?', $keywords)
                ->order('nb_hits DESC')
                ->limit(10);

            $results = $this->_tags->fetchAll($select);

            if (count($results) > 0) {
                $translate = $this->getTranslate();

                foreach ($results as $result) {
                    $categoryName = null;

                    if ($result['category_id']) {
                        $categoryName = $translate->_(
                            $result->findParentRow('\Ppb\Db\Table\Categories')->name);
                    }

                    $tags[] = array(
                        'value'        => $result['keywords'],
                        'tokens'       => $result['keywords'],
                        'categoryId'   => $result['category_id'],
                        'categoryName' => $categoryName,
                    );
                }

            }
        }

        $this->getResponse()->setHeader('Content-Type: application/json');

        $this->_view->setContent(
            json_encode($tags));

        return $this->_view;
    }

    public function Autocomplete()
    {
        $tags = array();

        $term = $this->getRequest()->getParam('term');

        $this->getResponse()->setHeader('Content-Type: application/json');

        if (!empty($term)) {
            $keywords = '%' . str_replace(' ', '%', $term) . '%';

            $select = $this->_tags->getTable()->select();
            $select->where('keywords LIKE ?', $keywords)
                ->order('nb_hits DESC')
                ->limit(10);

            $results = $this->_tags->fetchAll($select);

            if (count($results) > 0) {
                $translate = $this->getTranslate();

                foreach ($results as $result) {
                    $categoryName = null;
                    $label = $value = $result['keywords'];

                    if ($result['category_id']) {
                        $categoryName = $translate->_(
                            $result->findParentRow('\Ppb\Db\Table\Categories')->name);
                        $label .= ' '
                            . $translate->_('in')
                            . ' <span class="tagged-category">' . $categoryName . '</span>';
                    }

                    $tags[] = array(
                        'value'         => $value,
                        'label'         => $label,
                        'category_id'   => $result['category_id'],
                        'category_name' => $categoryName,
                    );
                }

            }
        }

        $this->getResponse()->setHeader('Content-Type: application/json');

        $this->_view->setContent(
            json_encode($tags));

        return $this->_view;
    }

}

