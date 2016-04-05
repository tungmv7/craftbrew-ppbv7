<?php

/**
 *
 * PHP Pro Bid $Id$ U5a83nKc27qHYcDOpkOS+bzBFnO+r4gqWe8tiIYKE5w=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * offer status view helper class
 */

namespace Listings\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\Offer as OfferModel;

class OfferStatus extends AbstractHelper
{

    /**
     *
     * offer status helper
     *
     * @param \Ppb\Db\Table\Row\Offer $offer
     *
     * @return string
     */
    public function offerStatus(OfferModel $offer, $enhanced = false)
    {
        $output = array();

        $translate = $this->getTranslate();

        if ($enhanced === true) {
            switch ($offer->getData('type')) {
                case 'offer':
                    $output[] = '<span class="label label-offer">' . $translate->_('Make Offer') . '</span>';
                    break;
                default:
                    $output[] = '<span class="label label-default">' . $translate->_('N/A') . '</span>';
                    break;
            }
        }

        switch ($offer->getData('status')) {
            case OfferModel::STATUS_ACCEPTED:
                $output[] = '<span class="label label-success">' . $translate->_(OfferModel::$statuses[OfferModel::STATUS_ACCEPTED]) . '</span>';
                break;
            case OfferModel::STATUS_DECLINED:
                $output[] = '<span class="label label-declined">' . $translate->_(OfferModel::$statuses[OfferModel::STATUS_DECLINED]) . '</span>';
                break;
            case OfferModel::STATUS_PENDING:
                $output[] = '<span class="label label-pending">' . $translate->_(OfferModel::$statuses[OfferModel::STATUS_PENDING]) . '</span>';
                break;
            case OfferModel::STATUS_WITHDRAWN:
                $output[] = '<span class="label label-withdrawn">' . $translate->_(OfferModel::$statuses[OfferModel::STATUS_WITHDRAWN]) . '</span>';
                break;
            case OfferModel::STATUS_COUNTER:
                $output[] = '<span class="label label-counter">' . $translate->_(OfferModel::$statuses[OfferModel::STATUS_COUNTER]) . '</span>';
                break;
            default:
                $output[] = '<span class="label">' . $translate->_('n/a') . '</span>';
                break;
        }

        return implode(' ', $output);
    }

}

