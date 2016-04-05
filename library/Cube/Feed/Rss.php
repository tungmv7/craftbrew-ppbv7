<?php
/**
 *
 * Cube Framework $Id$ 339kRt+P3V5JD8K1NR077H4zezovQin6nPZ9xuEEMJk=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * rss 2.0 feed class
 */

namespace Cube\Feed;

class Rss extends AbstractFeed
{


    /**
     *
     * generate feed based on entries
     *
     * @return string
     */
    public function generateFeed()
    {

        // header
        $output = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL
            . '<rss version="2.0">' . PHP_EOL
            . '<channel>' . PHP_EOL
            . $this->_renderArray($this->_channels);


        if ($this->_entries) {
            /** @var \Cube\Feed\Entry $entry */
            foreach ($this->_entries as $entry) {
                $output .= '<item>' . PHP_EOL;

                $elements = $entry->getElements();
                foreach ($elements as $key => $value) {
                    $output .= "<$key>$value</$key>" . PHP_EOL;
                }

                $output .= '</item>' . PHP_EOL;
            }
        }

        $output .= '</channel>' . PHP_EOL
            . '</rss>';

        return $output;

    }

}