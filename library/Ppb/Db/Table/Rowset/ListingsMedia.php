<?php

/**
 *
 * PHP Pro Bid $Id$ ReCBLmVGgLBaCgnOHQYB6i8sHmO54CF1gvHo41gp5G4=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * listings media table rowset class
 */

namespace Ppb\Db\Table\Rowset;

class ListingsMedia extends AbstractRowset
{

    /**
     *
     * row object class
     *
     * @var string
     */
    protected $_rowClass = '\Ppb\Db\Table\Row\ListingMedia';

    public function getFormattedData()
    {
        $data = array();
        $types = array();
        foreach ($this->_data as $row) {
            $mediaType = $row['type'];
            if (!in_array($mediaType, $types)) {
                array_push($types, $mediaType);
            }

            $data[$mediaType][] = $row['value'];
        }

        return $data;
    }

}

