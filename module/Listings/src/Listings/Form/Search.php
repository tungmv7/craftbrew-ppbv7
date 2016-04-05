<?php

/**
 *
 * PHP Pro Bid $Id$ 3bARaqYd4VApuBcyngYqo0oK7TuJhMrn6rVBI99qSjY=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * listing search form
 */

namespace Listings\Form;

use Ppb\Model\Elements,
    Ppb\Form\AbstractBaseForm;

class Search extends AbstractBaseForm
{

    const SUBMIT_SEARCH = 'submit_search';
    /**
     *
     * search elements model
     *
     * @var \Ppb\Model\Elements\Search
     */
    protected $_model;

    /**
     *
     * submit buttons - overridden by child methods
     *
     * @var array
     */
    protected $_buttons = array(
        self::SUBMIT_SEARCH => 'Search',
    );

    /**
     *
     * class constructor
     *
     * @param string|array                $formId         the id of the form, used by the form elements model
     * @param string                      $action         the form's action
     * @param \Ppb\Db\Table\Row\User|null $store          in case we are searching in a store
     * @param \Cube\Db\Select             $listingsSelect listings select object, used for counters display
     */
    public function __construct($formId = null, $action = null, $store = null, $listingsSelect = null)
    {
        parent::__construct($action);

        $this->setTitle('Listings Search');

        if (is_array($formId)) {
            $this->_includedForms = array_merge($this->_includedForms, $formId);
        }
        else if ($formId !== null) {
            array_push($this->_includedForms, $formId);
        }

        $this->setMethod(self::METHOD_GET);
        $this->_model = new Elements\Search($formId);
        $this->_model->setStore($store)
            ->setListingsSelect($listingsSelect);

        $this->addElements(
            $this->_model->getElements());

        $this->addSubmitElement('Search', self::SUBMIT_SEARCH);

        $this->setPartial('forms/search.phtml');
    }

    public function generateBasicForm()
    {
        // temporary
        $this->getElement(self::SUBMIT_SEARCH)
            ->clearAttributes()
            ->setAttributes(array(
                'class' => 'btn btn-primary',
            ));


        $this->setPartial('forms/basic-search.phtml');

        return $this;
    }

    /**
     *
     * method to create a form element from an array
     *
     * @param array $elements
     * @param bool  $allElements
     * @param bool  $clearElements
     *
     * @return $this
     */
    public function addElements(array $elements, $allElements = false, $clearElements = true)
    {
        parent::addElements($elements, $allElements, $clearElements);

        if ($this->hasElement('csrf')) {
            $this->removeElement('csrf');
        }

        return $this;
    }
    /**
     *
     * set the data of the submitted form
     * plus add the data in the search model
     *
     * @param array $data form data
     *
     * @return \Listings\Form\Search
     */

    /**
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        $this->_model->setData($data);
        $this->addElements(
            $this->_model->getElements());

        $this->addSubmitElement('Search', self::SUBMIT_SEARCH);

        parent::setData($data);

        return $this;
    }

}