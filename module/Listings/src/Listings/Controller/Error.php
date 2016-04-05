<?php

/**
 *
 * PHP Pro Bid $Id$ 0z4tPiTFQq4r1US8AeEDmX3x27Cdq4u1iZIUUnvceL4=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */

namespace Listings\Controller;

use Ppb\Controller\Action\AbstractAction;

class Error extends AbstractAction
{

    public function init()
    {
        $this->getResponse()
                ->setHeader(' ')
                ->setResponseCode(404);
    }

    public function NotFound()
    {
        return array();
    }

    public function Prohibited()
    {
        return array();
    }

    public function NoListing()
    {
        return array();
    }

}

