<?php

/**
 *
 * PHP Pro Bid $Id$ opi9PgPNrj0liU7PAJb8cpqYeYQxwIv/r2SZiiMBDfw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * sale status & options view helper class
 */

namespace Listings\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\Sale as SaleModel;

class SaleOptions extends AbstractHelper
{

    /**
     *
     * sale status options helper
     *
     * @param \Ppb\Db\Table\Row\Sale $sale
     *
     * @return string
     */
    public function saleOptions(SaleModel $sale)
    {
        $output = array();

        $translate = $this->getTranslate();

        switch ($sale->getData('flag_payment')) {

            case SaleModel::PAYMENT_PAID:
                $output[] = '<span class="fa-stack">
                        <i class="fa fa-square fa-stack-2x flag-success"></i>
                        <i class="fa fa-usd fa-stack-1x fa-inverse" title="' . $translate->_(SaleModel::$paymentStatuses[SaleModel::PAYMENT_PAID]) . '"></i>
                    </span>';
                break;
            case SaleModel::PAYMENT_PAID_DIRECT_PAYMENT:
                $output[] = '<span class="fa-stack">
                        <i class="fa fa-square fa-stack-2x flag-success"></i>
                        <i class="fa fa-usd fa-stack-1x fa-inverse" title="' . $translate->_(SaleModel::$paymentStatuses[SaleModel::PAYMENT_PAID_DIRECT_PAYMENT]) . '"></i>
                    </span>';
                break;
            case SaleModel::PAYMENT_PAY_ARRIVAL:
                $output[] = '<span class="fa-stack">
                        <i class="fa fa-square fa-stack-2x flag-pay-arrival"></i>
                        <i class="fa fa-usd fa-stack-1x fa-inverse" title="' . $translate->_(SaleModel::$paymentStatuses[SaleModel::PAYMENT_PAY_ARRIVAL]) . '"></i>
                    </span>';
                break;
            case SaleModel::PAYMENT_UNPAID:
            default:
                $output[] = '<span class="fa-stack">
                        <i class="fa fa-square fa-stack-2x flag-pending"></i>
                        <i class="fa fa-usd fa-stack-1x fa-inverse" title="' . $translate->_(SaleModel::$paymentStatuses[SaleModel::PAYMENT_UNPAID]) . '"></i>
                    </span>';
                break;
        }

        switch ($sale->getData('flag_shipping')) {

            case SaleModel::SHIPPING_SENT:
                $output[] = '<span class="fa-stack">
                        <i class="fa fa-square fa-stack-2x flag-success"></i>
                        <i class="fa fa-envelope fa-stack-1x fa-inverse" title="' . $translate->_(SaleModel::$shippingStatuses[SaleModel::SHIPPING_SENT]) . '"></i>
                    </span>';
                break;
            case SaleModel::SHIPPING_PROBLEM:
                $output[] = '<span class="fa-stack">
                        <i class="fa fa-square fa-stack-2x flag-failed"></i>
                        <i class="fa fa-envelope fa-stack-1x fa-inverse" title="' . $translate->_(SaleModel::$shippingStatuses[SaleModel::SHIPPING_PROBLEM]) . '"></i>
                    </span>';
                break;
            case SaleModel::SHIPPING_NA:
                $output[] = '<span class="fa-stack">
                        <i class="fa fa-square fa-stack-2x flag-na"></i>
                        <i class="fa fa-envelope fa-stack-1x fa-inverse" title="' . $translate->_(SaleModel::$shippingStatuses[SaleModel::SHIPPING_NA]) . '"></i>
                    </span>';
                break;
            case SaleModel::SHIPPING_PROCESSING:
            default:
                $output[] = '<span class="fa-stack">
                        <i class="fa fa-square fa-stack-2x flag-pending"></i>
                        <i class="fa fa-envelope fa-stack-1x fa-inverse" title="' . $translate->_(SaleModel::$shippingStatuses[SaleModel::SHIPPING_PROCESSING]) . '"></i>
                    </span>';
                break;
        }


        return implode(' ', $output);
    }

}

