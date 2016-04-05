<?php

/**
 * 
 * PHP Pro Bid $Id$ Zd2sW/4k5nOCpJhRA3TWmCZJq/1C/6P+xF2ibsRtABc=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * bid status view helper class
 */

namespace Listings\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\Bid as BidModel;

class BidStatus extends AbstractHelper
{

    /**
     * 
     * bid status helper
     * 
     * @param \Ppb\Db\Table\Row\Bid $bid
     * @return string
     */
    public function bidStatus(BidModel $bid)
    {
        $translate = $this->getTranslate();

        if ($bid->getData('outbid')) {
            return '<span class="label label-declined">' . $translate->_(BidModel::$statuses[BidModel::STATUS_OUTBID]) . '</span>';
        }
        else {
            return '<span class="label label-success">' . $translate->_(BidModel::$statuses[BidModel::STATUS_HIGH_BID]) . '</span>';
        }
    }

}

