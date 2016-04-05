<?php

/**
 * 
 * PHP Pro Bid $Id$ Ohh0kPEs1cJJzroq5jCTcwBIcCPcQHG/7+0DESyBJUg=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * make offer ranges view helper class
 */

namespace Listings\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\Listing as ListingModel;

class OfferRanges extends AbstractHelper
{

    /**
     * 
     * offer ranges helper
     * 
     * @param \Ppb\Db\Table\Row\Listing $listing
     * @return string
     */
    public function offerRanges(ListingModel $listing)
    {
        $translate = $this->getTranslate();

        if ($listing['make_offer_min'] <= 0 && $listing['make_offer_max'] <= 0) {
            return $translate->_('All offers accepted');
        }

        $output = $translate->_('Offers accepted:') . ' ';

        $view = $this->getView();

        if ($listing['make_offer_min'] > 0) {
            $output .= sprintf(
                $translate->_('from %s'),
                $view->amount($listing['make_offer_min'], $listing['currency'])) . ' ';
        }
        if ($listing['make_offer_max'] > 0) {
            $output .= sprintf(
                $translate->_('to %s'),
                $view->amount($listing['make_offer_max'], $listing['currency']));
        }

        return $output;
    }

}

