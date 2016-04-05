<?php

/**
 *
 * PHP Pro Bid $Id$ bbkDUj+LKTo+DKydFNBa5NbNk1krnWr7efou9FC1Axw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * content sections table row object model
 */

namespace Ppb\Db\Table\Row;

class ContentSection extends AbstractRow
{
    /**
     *
     * generate link for content section
     *
     * @return array
     */
    public function link()
    {
        if ($slug = $this->getData('slug')) {
            return $slug;
//            return array(
//                'module'     => 'app',
//                'controller' => 'sections',
//                'action'     => 'view',
//                'id'         => $this->getData('id'),
//            );
        }
        else {
            return array(
                'module'     => 'app',
                'controller' => 'sections',
                'action'     => 'view',
                'name'       => $this->getData('name'),
                'id'         => $this->getData('id'),
            );
        }
    }

}

