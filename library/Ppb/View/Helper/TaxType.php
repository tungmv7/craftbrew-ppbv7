<?php

/**
 *
 * PHP Pro Bid $Id$ 4v95KhKDU7EJYdIQ8y+6K7U2GWF1qe4bNsgHfa5lW0A=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * tax types details view helper class
 */

namespace Ppb\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\TaxType as TaxTypeModel,
    Ppb\Service;

class TaxType extends AbstractHelper
{

    /**
     *
     * tax type model
     *
     * @var \Ppb\Db\Table\Row\TaxType
     */
    protected $_taxType;

    /**
     *
     * locations table service
     *
     * @var \Ppb\Service\Table\Relational\Locations
     */
    protected $_locations;

    /**
     *
     * main method, only returns object instance
     *
     * @param int|string|\Ppb\Db\Table\Row\TaxType $taxType
     *
     * @return $this
     */
    public function taxType($taxType = null)
    {
        if ($taxType !== null) {
            $this->setTaxType($taxType);
        }

        return $this;
    }

    /**
     *
     * get tax type model
     *
     * @return \Ppb\Db\Table\Row\TaxType
     * @throws \InvalidArgumentException
     */
    public function getTaxType()
    {
        if (!$this->_taxType instanceof TaxTypeModel) {
            throw new \InvalidArgumentException("The tax type model has not been instantiated");
        }

        return $this->_taxType;
    }

    /**
     *
     * set tax type model
     *
     * @param \Ppb\Db\Table\Row\TaxType $taxType
     *
     * @return $this
     */
    public function setTaxType(TaxTypeModel $taxType)
    {
        $this->_taxType = $taxType;

        return $this;
    }


    /**
     *
     * get locations table service
     *
     * @return \Ppb\Service\Table\Relational\Locations
     */
    public function getLocations()
    {
        if (!$this->_locations instanceof Service\Table\Relational\Locations) {
            $this->setLocations(
                new Service\Table\Relational\Locations());
        }

        return $this->_locations;
    }

    /**
     *
     * set locations table service
     *
     * @param \Ppb\Service\Table\Relational\Locations $locations
     *
     * @return $this
     */
    public function setLocations(Service\Table\Relational\Locations $locations)
    {
        $this->_locations = $locations;

        return $this;
    }

    /**
     *
     * display tax description (for sellers use)
     *
     * @param string $separator
     *
     * @return string
     */
    public function description($separator = ' - ')
    {
        $output = array();

        $taxType = $this->getTaxType();

        $translate = $this->getTranslate();

        $output[] = $translate->_($taxType['name']);
        $output[] = $translate->_($taxType['description']);
        $output[] = $taxType['amount'] . '%';


        return implode($separator, $output);
    }

    /**
     *
     * display details on the tax that will apply
     *
     * @return string
     */
    public function display()
    {
        $taxType = $this->getTaxType();

        $translate = $this->getTranslate();

        return sprintf($translate->_('%s %s will be applied to the purchase price'),
            $taxType['amount'] . '%', $taxType['description']);
    }
}

