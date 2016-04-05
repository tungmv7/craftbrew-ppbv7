<?php

/**
 *
 * PHP Pro Bid $Id$ 32ivxrCmaKvWJeQ6YMcg0BGdwkDV4COPAdp9wQwJASA=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * categories table row object model
 */

namespace Ppb\Db\Table\Row;

class Category extends AbstractRow
{
    /**
     *
     * generate link for categories browse pages
     *
     * @return array
     */
    public function link()
    {

        if ($slug = $this->getData('slug')) {
            return array(
                'module'        => 'listings',
                'controller'    => 'browse',
                'action'        => 'index',
                'category_slug' => $slug,
            );
        }
        else {
            return array(
                'module'        => 'listings',
                'controller'    => 'browse',
                'action'        => 'index',
                'category_name' => $this->getData('name'),
                'parent_id'     => $this->getData('id'),
            );

        }
    }

    /**
     *
     * generate link for categories list pages
     *
     * @return array
     */
    public function browseLink()
    {
        $slug = $this->getData('slug');

        if (!empty($slug)) {
            return array(
                'module'        => 'listings',
                'controller'    => 'categories',
                'action'        => 'browse',
                'category_slug' => $slug
            );
        }
        else {
            return array(
                'module'        => 'listings',
                'controller'    => 'categories',
                'action'        => 'browse',
                'category_name' => $this->getData('name'),
                'parent_id'     => $this->getData('id'),
            );
        }
    }

    /**
     *
     * get counter by listing type(s)
     *
     * @param string|array $filters listing types filter
     *
     * @return int
     */
    public function getCounter($filters = null)
    {
        $data = \Ppb\Utility::unserialize($this->getData('counter'));

        if ($filters === null) {
            $filters = array('auction', 'product');
        }

        $filters = (!is_array($filters)) ? array($filters) : $filters;

        $counter = 0;

        foreach ($filters as $value) {
            if (isset($data[$value])) {
                $counter += intval($data[$value]);
            }
        }

        return $counter;
    }

    /**
     *
     * category counter - addition operation
     *
     * @param string $listingType
     *
     * @return $this
     */
    public function addCounter($listingType)
    {
        $data = \Ppb\Utility::unserialize($this->getData('counter'));

        if (isset($data[$listingType])) {
            $data[$listingType]++;
        }
        else {
            $data[$listingType] = 1;
        }

        $this->save(array(
            'counter' => serialize($data)
        ));

        return $this;
    }

    /**
     *
     * category counter - subtraction operation
     *
     * @param string $listingType
     *
     * @return $this
     */
    public function subtractCounter($listingType)
    {
        $data = \Ppb\Utility::unserialize($this->getData('counter'));

        if (isset($data[$listingType])) {
            $data[$listingType]--;
        }
        else {
            $data[$listingType] = 0;
        }

        if ($data[$listingType] < 0) {
            $data[$listingType] = 0;
        }

        $this->save(array(
            'counter' => serialize($data)
        ));

        return $this;
    }

}

