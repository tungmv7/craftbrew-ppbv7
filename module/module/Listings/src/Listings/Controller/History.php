<?php

/**
 *
 * PHP Pro Bid $Id$ vB8Uhj5bmrZfl+HHpzVPSh044VMySGA2l+nzrIBnmd8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */

namespace Listings\Controller;

use Ppb\Controller\Action\AbstractAction,
        Ppb\Service;

class History extends AbstractAction
{

    /**
     *
     * listings service
     *
     * @var \Ppb\Db\Table\Row\Listing
     */
    protected $_listing;

    public function init()
    {
        $listingsService = new Service\Listings();
        $this->_listing = $listingsService->findBy('id', (int)$this->getRequest()->getParam('id'));
    }

    public function Bids()
    {
        return array(
            'listing' => $this->_listing,
        );
    }

    public function Offers()
    {
        return array(
            'listing' => $this->_listing,
        );
    }

    public function Sales()
    {
        return array(
            'listing' => $this->_listing,
        );
    }

}

