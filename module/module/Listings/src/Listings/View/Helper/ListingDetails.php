<?php

/**
 *
 * PHP Pro Bid $Id$ wX0S8X5LSsWklo9Dvy75OCVvzK+DIJ7yuzJs3zlPY+4=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * listing details view helper class
 */

namespace Listings\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Service,
    Ppb\Db\Table\Row\Listing,
    Ppb\Model\Shipping as ShippingModel;

class ListingDetails extends AbstractHelper
{

    /**
     *
     * listing model
     *
     * @var \Ppb\Db\Table\Row\Listing
     */
    protected $_listing;

    /**
     *
     * main method, only returns object instance
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     *
     * @return $this
     */
    public function listingDetails(Listing $listing = null)
    {
        if ($listing !== null) {
            $this->setListing($listing);
        }

        return $this;
    }

    public function getListing()
    {
        if (!$this->_listing instanceof Listing) {
            throw new \InvalidArgumentException("The listing model has not been instantiated");
        }

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
    public function setListing(Listing $listing)
    {
        if (!$listing instanceof Listing) {
            throw new \InvalidArgumentException("The listing model must be an instance of \Ppb\Db\Table\Row\Listing");
        }

        $this->_listing = $listing;

        return $this;
    }

    /**
     *
     * display listing location
     *
     * @param bool $detailed display state as well
     *
     * @return string
     */
    public function location($detailed = true)
    {
        $location = array();

        $listing = $this->getListing();
        $translate = $this->getTranslate();

        $country = $this->_getLocationName($listing->getData('country'));
        if ($country !== null) {
            $location[] = $translate->_($country);
        }

        if ($detailed === true) {
            $state = $this->_getLocationName($listing->getData('state'));
            if ($state !== null) {
                $location[] = $translate->_($state);
            }

            $address = $listing->getData('address');
            if ($address !== null) {
                $location[] = $address;
            }
        }

        return (($output = implode(', ', array_reverse($location))) != '') ? $output : null;
    }

    /**
     *
     * display listing status
     *
     * @param bool $detailed
     *
     * @return string
     */
    public function status($detailed = true)
    {
        $output = array();

        $listing = $this->getListing();

        $translate = $this->getTranslate();

        if ($listing['draft']) {
            $output[] = '<span class="label label-draft">' . $translate->_('Draft') . '</span>';
        }
        else {
            if ($detailed === true) {
                switch ($listing['list_in']) {
                    case 'site':
                        $output[] = '<span class="label label-listin-site">' . $translate->_('Listed in Site') . '</span>';
                        break;
                    case 'store':
                        $output[] = '<span class="label label-listin-store">' . $translate->_('Listed in Store') . '</span>';
                        break;
                    case 'both':
                        $output[] = '<span class="label label-listin-both">' . $translate->_('Listed in Both') . '</span>';
                        break;
                }

                if ($listing['hpfeat']) {
                    $output[] = '<span class="label label-featured">' . $translate->_('Featured') . '</span>';
                }

                if (!$listing['approved']) {
                    $output[] = '<span class="label label-default">' . $translate->_('Unapproved') . '</span>';
                }
                else if ($listing['active'] == -1) {
                    $output[] = '<span class="label label-danger">' . $translate->_('Admin Suspended') . '</span>';
                }
                else if ($listing['active'] == 0) {
                    $output[] = '<span class="label label-warning">' . $translate->_('Suspended') . '</span>';
                }
                else {
                    $output[] = '<span class="label label-success">' . $translate->_('Active') . '</span>';
                }
            }

            switch ($listing->getStatus()) {
                case Listing::SCHEDULED:
                    $output[] = '<span class="label label-scheduled">' . $translate->_('Scheduled') . '</span>';
                    break;
                case Listing::CLOSED:
                    $output[] = '<span class="label label-closed">' . $translate->_('Closed') . '</span>';
                    break;
                case Listing::OPEN:
                    $output[] = '<span class="label label-open">' . $translate->_('Open') . '</span>';
                    break;
            }

            if ($detailed === true) {
                if ($listing['deleted']) {
                    $output[] = '<span class="label label-default">' . $translate->_('Deleted') . '</span>';
                }
            }
        }


        return implode('', $output);
    }

    /**
     *
     * this method will display the locations where the listing will ship to
     * we will always insert the listing in the shipping model, to get the item's location when using
     * domestic shipping
     *
     *
     * @param \Ppb\Model\Shipping $shipping
     *
     * @return string|null
     */
    public function shipsTo(ShippingModel $shipping)
    {
        $shipping->addData(
            $this->getListing());

        $postageSettings = $shipping->getPostageSettings();

        if (isset($postageSettings[ShippingModel::SETUP_SHIPPING_LOCATIONS])) {
            if ($postageSettings[ShippingModel::SETUP_SHIPPING_LOCATIONS] === ShippingModel::POSTAGE_LOCATION_WORLDWIDE) {
                return $this->getTranslate()->_('Worldwide');
            }
        }

        $shippableLocations = array_filter(
            array_values($shipping->getShippableLocations()));

        if (count($shippableLocations) > 0) {
            $locationsService = new Service\Table\Relational\Locations();

            return implode(', ', $locationsService->getMultiOptions(array_values($shippableLocations)));
        }


        return null;
    }

    /**
     *
     * display available quantity - can be based on selected product attributes as well
     *
     * @param array|null $productAttributes
     *
     * @return int|string|true
     */
    public function availableQuantity($productAttributes = null)
    {
        $listing = $this->getListing();

        $translate = $this->getTranslate();

        $quantity = $listing->getAvailableQuantity(null, $productAttributes);

        if ($listing['listing_type'] == 'product') {
            /** @var \Ppb\Db\Table\Row\User $user */
            $user = $listing->findParentRow('\Ppb\Db\Table\Users');


            if ($user->getGlobalSettings('quantity_description')) {
                $lowStockThreshold = $user->getGlobalSettings('quantity_low_stock');
                $lowStockThreshold = ($lowStockThreshold > 0) ? $lowStockThreshold : 1;

                if ($quantity > $lowStockThreshold || $quantity === true) {
                    $quantity = $translate->_('In Stock');
                }
                else if ($quantity > 0) {
                    $quantity = $translate->_('Low Stock');
                }
                else {
                    $quantity = $translate->_('Out of Stock');
                }
            }
        }

        return ($quantity === true) ? $translate->_('In Stock') : $quantity;
    }


    /**
     *
     * get the name of a location based on its id
     *
     * @param int|string $location
     * @param string     $key
     *
     * @return array|null|string
     */
    protected function _getLocationName($location, $key = 'name')
    {
        if (empty($location)) {
            return null;
        }
        if (is_numeric($location)) {
            $locations = new Service\Table\Relational\Locations();
            $row = $locations->findBy('id', (int)$location);
            if ($row != null) {
                $location = $row->getData($key);
            }
        }

        return $location;
    }

}

