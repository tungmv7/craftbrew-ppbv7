<?php

/**
 *
 * PHP Pro Bid $Id$ 5c2dm0ER/XiezD4etD1mdLvxk22L2188e9fjzFOTFKY=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * shopping cart drop-down button/menu view helper class
 */

namespace Listings\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Cube\Controller\Front,
    Ppb\Service,
    Ppb\Db\Table\Row\User as UserModel;

class CartDropdown extends AbstractHelper
{

    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings;

    public function __construct(array $settings)
    {
        $this->setSettings($settings);
    }

    /**
     *
     * set settings array
     *
     * @param array $settings
     *
     * @return $this
     */
    public function setSettings(array $settings)
    {
        $this->_settings = $settings;

        return $this;
    }

    /**
     *
     * generate cart dropdown button/menu
     * the helper will accept a custom template
     * reserved variables:
     * %price%
     * %items%
     * %dropDown%
     *
     * @param string $template
     *
     * @return null|string
     */
    public function cartDropdown($template = null)
    {
        $output = null;

        if ($this->_settings['enable_shopping_cart']) {
            $view = $this->getView();
            $bootstrap = Front::getInstance()->getBootstrap();
            /** @var \Cube\Session $session */
            $session = $bootstrap->getResource('session');
            $userToken = strval($session->getCookie(UserModel::USER_TOKEN));

            $translate = $this->getTranslate();

            $currenciesService = new Service\Table\Currencies();
            $salesService = new Service\Sales();

            $sales = $salesService->fetchAll(
                $salesService->getTable()->select()
                    ->where('pending = ?', 1)
                    ->where('user_token = ?', $userToken)
                    ->order(array('updated_at DESC', 'created_at DESC'))
            );

            $nbItems = $price = 0;

            $dropDown = '';

            if (count($sales) > 0) {
                /** @var \Ppb\Db\Table\Row\Sale $sale */
                foreach ($sales as $sale) {

                    $dropDown .= '
                        <div class="table-responsive">
                            <table class="table table-condensed">
                                <thead>
                                <tr>
                                    <th class="size-small"></th>
                                    <th>' . $translate->_('Item Description') . '</th>
                                    <th class="size-mini">' . $translate->_('Qty') . '</th>
                                    <th class="size-mini">' . $translate->_('Subtotal') . '</th>
                                </tr>
                                </thead>
                                <tbody>';

                    $salesListings = $sale->findDependentRowset('\Ppb\Db\Table\SalesListings');

                    /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
                    foreach ($salesListings as $saleListing) {
                        $nbItems++;

                        /** @var \Ppb\Db\Table\Row\Listing $listing */
                        $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings');

                        $price += $currenciesService->convertAmount($saleListing['quantity'] * $saleListing['price'], $sale['currency'], $this->_settings['currency']);

                        if ($listing) {
                            $link = $view->url($listing->link());
                            $mainImage = $listing->getMainImage();
                            $listingName = $listing['name'];
                        }
                        else {
                            $link = '#';
                            $mainImage = null;
                            $listingName = $translate->_('Listing Deleted');
                        }
                        $dropDown .= '
                            <tr>
                                <td><a href="' . $link . '">' . $view->thumbnail($mainImage, 50, true, array('alt' => $listingName)) . '</a></td>
                                <td>
                                    <div>
                                        <a href="' . $link . '">' . $listingName . '</a>
                                    </div>
                                    ' . ((!empty($saleListing['product_attributes'])) ? '
                                    <div>
                                        <small>' . $view->productAttributes($saleListing['product_attributes'])->display() . '</small>
                                    </div>' : '') . '
                                </td>
                                <td>' . $saleListing['quantity'] . '</td>
                                <td>' . $view->amount(($saleListing['quantity'] * $saleListing['price']), $sale['currency']) . '</td>
                            </tr>';
                    }

                    $dropDown .= '
                                <tr>
                                    <td colspan="2"></td>
                                    <td><strong>' . $translate->_('Total') . '</strong></td>
                                    <td>' . $view->amount($sale->calculateTotal(true, false), $sale['currency']) . '</td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <div class="pull-right">
                                            <a href="' . $view->url(array('module' => 'listings', 'controller' => 'cart', 'action' => 'index', 'id' => $sale['id'])) . '"
                                               class="btn btn-default btn-sm">' . $translate->_('View Cart') . '</a>
                                            <a href="' . $view->url(array('module' => 'listings', 'controller' => 'cart', 'action' => 'checkout', 'id' => $sale['id'])) . '"
                                               class="btn btn-default btn-sm">' . $translate->_('Checkout') . '</a>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>';
                }
            }
            else {
                $dropDown .= '
                    <div class="text-center">
                        <small>' . $translate->_('The shopping cart is empty.') . '</small>
                    </div>';
            }

            if ($template === null) {
                $content = $translate->_('Cart');
                if ($nbItems > 0) {
                    $content .= ' &middot; %price% (%items%)';
                }

                $template = '<button class="btn btn-default btn-shopping-cart-dropdown dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-shopping-cart"></i>
                        <span>' . $content . '</span>
                    </button>
                    <div class="shopping-cart-dropdown dropdown-menu">
                        %dropDown%
                    </div>';
            }

            $output = str_replace(
                    array('%price%', '%items%', '%dropDown%'),
                    array($view->amount($price, null, null, true), $nbItems, $dropDown),
                    $template);

        }

        return $output;
    }

}

