<?php

/**
 *
 * PHP Pro Bid $Id$ pJRyVfo/bRA+EAA7d8Ppk+q8sEPG/Pr77jb4dBsgksE=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * postage method select element
 *
 * requires the following input:
 *
 * data: id - quantity combination
 * location id: destination location
 * zip code: destination zip/post code
 */

namespace Ppb\Form\Element;

use Cube\Form\Element\Select,
    Cube\Form\Element\Hidden,
    Cube\Controller\Front,
    Ppb\Service,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Model\Shipping as ShippingModel;

class PostageMethod extends Select
{

    const ELEMENT_CLASS = 'postage-method';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'PostageMethod';


    /**
     *
     * add single multi option key value pair
     *
     * @param string $key
     * @param string $value
     * @param string $price
     * @param string $currency
     *
     * @return \Cube\Form\Element
     */
    public function addMultiOption($key, $value, $price = null, $currency = null)
    {
        $this->_multiOptions[$key] = array(
            'value'    => $value,
            'price'    => $price,
            'currency' => $currency
        );

        return $this;
    }

    /**
     *
     * enable shipping model
     *
     * @param array  $input      listing id - quantity key pairs
     * @param int    $locationId destination location id
     * @param string $postCode   destination post code
     *
     * @return \Ppb\Form\Element\PostageMethod
     */
    public function setShippingModel($input, $locationId, $postCode)
    {
        $listingsService = new Service\Listings();

        $ownerId = null;
        $user = null;

        $data = array();

        $this->clearMultiOptions();

        foreach ($input as $id => $quantity) {
            $listing = $listingsService->findBy('id', $id);

            $quantity = ($quantity > 1) ? $quantity : 1;

            if ($ownerId === null || $listing['user_id'] == $ownerId) {
                $data[] = array(
                    'listing'  => $listing,
                    'quantity' => $quantity,
                );

                if ($ownerId === null) {
                    $user = $listing->findParentRow('\Ppb\Db\Table\Users');
                    $ownerId = $listing['user_id'];
                }
            }
        }

        $postage = array();

        if ($user instanceof UserModel) {
            $shippingModel = new ShippingModel($user);

            $shippingModel->setLocationId($locationId)
                ->setPostCode($postCode);

            foreach ($data as $row) {
                $shippingModel->addData($row['listing'], $row['quantity']);
            }

            try {
                $postage = $shippingModel->calculatePostage();
            } catch (\RuntimeException $e) {
            }
        }

        $view = Front::getInstance()->getBootstrap()->getResource('view');

        foreach ($postage as $key => $row) {
            $attribute = $row['method'] .
                (($key == ShippingModel::KEY_PICK_UP) ? '' : ' - ' . $view->amount($row['price'],
                        $row['currency']));

            $this->addMultiOption($key, $attribute, $row['price'], $row['currency']);
        }

        return $this;
    }

    public function render()
    {
        $output = null;
        $value = $this->getValue();

        if (count($this->_multiOptions) > 0) {
            $multiple = null;
            if ($this->getMultiple() === true) {
                $multiple = '[]';
            }

            $output = '<select name="' . $this->_name . $multiple . '" '
                . $this->renderAttributes() . '>';

            foreach ((array)$this->_multiOptions as $key => $row) {
                $selected = (in_array($key, (array)$value)) ? ' selected' : '';
                $output .= '<option value="' . $key . '"' . $selected
                    . ' data-currency="' . $row['currency'] . '"'
                    . ' data-price="' . $row['price'] . '">'
                    . $row['value'] . '</option>';
            }

            $output .= '</select>';
        }
        else {
            $translate = $this->getTranslate();

            $hidden = new Hidden($this->_name);
            $hidden->setValue($value)->setMultiple(
                $this->getMultiple());

            $output = '<div class="alert alert-danger">' . $translate->_('No shipping methods available.') . '</div>' . $hidden->render();
        }


        return $output;
    }

}
