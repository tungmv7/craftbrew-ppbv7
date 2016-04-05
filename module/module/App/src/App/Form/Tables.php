<?php

/**
 *
 * PHP Pro Bid $Id$ XawglDQOw4s2PcwCOaoJEpJ++iNwzvgm0DyUuQXEmJM=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * tables form
 *
 */

namespace App\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Service\Table;

class Tables extends AbstractBaseForm
{

    const BTN_SUBMIT = 'btn_submit';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Update',
    );

    /**
     *
     * service table used
     *
     * @var \Ppb\Service\Table\AbstractServiceTable
     * @var string $action
     * @var bool   $inAdmin
     */
    protected $_service;

    public function __construct(Table\AbstractServiceTable $serviceTable, $action = null, $inAdmin = false)
    {
        parent::__construct($action);
        $this->setMethod(self::METHOD_POST);

        $this->setService($serviceTable);

        $elements = $serviceTable->getElements();

        foreach ($elements as $element) {
            $formElement = $this->createElementFromArray($element);
            $formElement->setMultiple();

            $this->addElement($formElement);
        }

        if ($serviceTable instanceof Table\Relational\AbstractServiceTableRelational) {
            $parentId = $this->createElement('hidden', 'parent_id');
            $this->addElement($parentId);
        }

        if (count($elements) > 0) {
            /* submit button */
            $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

            $this->getView()->columns = $serviceTable->getColumns();
            $this->getView()->insertRows = $serviceTable->getInsertRows();

            $this->setPartial('forms/tables.phtml');
        }
    }

    public function getService()
    {
        return $this->_service;
    }

    public function setService(Table\AbstractServiceTable $service)
    {
        $this->_service = $service;

        return $this;
    }

    /**
     *
     * custom save data method, which includes a flip array option, since
     * the data from a post operation needs to be flipped
     * in order to work for these types of forms
     *
     * @param array $data
     * @param bool  $flip
     *
     * @return \Cube\Form
     */
    public function setData(array $data = null, $flip = false)
    {
        if ($flip === true) {
            $data = array_replace_recursive($this->getData(), $this->_flipArray($data));
        }

        return parent::setData($data);
    }

    /**
     *
     * flip array for usage with the table form
     *
     * @param array $array
     *
     * @return array
     */
    protected function _flipArray(array $array)
    {
        $output = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $output[$k][$key] = $v;
                }
            }
            else {
                $output[$key] = $value;
            }
        }

        return $output;
    }

}
