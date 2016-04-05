<?php

/**
 *
 * PHP Pro Bid $Id$ HLzgoFdkIgsa9c4JugB5rT57kuoyQnP4KzedlAX3IDo=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * listings media table row object model
 */

namespace Ppb\Db\Table\Row;

use Ppb\Model\Uploader,
        Ppb\Service;

class ListingMedia extends AbstractRow
{

    /**
     *
     * delete row from listings media table, and also delete the corresponding uploaded file
     *
     * @return int
     */
    public function delete()
    {
        $fileName = $this->getData('value');
        $uploadType = $this->getData('type');

        $result = parent::delete();

        $uploader = new Uploader();
        $uploader->remove($fileName, $uploadType);

        return $result;
    }
}

