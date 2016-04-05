<?php
/**
 *
 * Cube Framework $Id$ 6lIaGwelv0ShAATB3q47xjfGJ9RwtR3wGiLtCmcHWis=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.6
 */
/**
 * abstract feed class
 */
namespace Cube\Feed;


abstract class AbstractFeed
{

    /**
     *
     * feed entries
     *
     * @var array
     */
    protected $_entries = array();

    /**
     *
     * channels
     *
     * @var array
     */
    protected $_channels = array();

    /**
     * @param array $entries
     *
     * @return $this
     */
    public function setEntries($entries)
    {
        foreach ((array)$entries as $entry) {
            $this->addEntry($entry);
        }

        return $this;
    }

    /**
     *
     * add entry to entries array
     *
     * @param \Cube\Feed\Entry $entry
     *
     * @return $this
     */
    public function addEntry(Entry $entry)
    {
        $this->_entries[] = $entry;

        return $this;
    }

    /**
     * @return array
     */
    public function getEntries()
    {
        return $this->_entries;
    }

    /**
     *
     * clear entries array
     *
     * @return $this
     */
    public function clearEntries()
    {
        $this->_entries = array();

        return $this;
    }


    /**
     *
     * set channels
     *
     * @param array $channels
     *
     * @return $this
     */
    public function setChannels($channels)
    {

        foreach ((array)$channels as $key => $value) {
            $this->addChannel($key, $value);
        }

        return $this;
    }

    /**
     *
     * add channel
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function addChannel($key, $value)
    {
        $this->_channels[$key] = $this->_formatString($value);

        return $this;
    }

    /**
     *
     * get channels
     *
     * @return array
     */
    public function getChannels()
    {
        return $this->_channels;
    }

    /**
     *
     * render one channel/element
     *
     * @param array $array
     *
     * @return null|string
     */
    protected function _renderArray($array)
    {
        $output = '';

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $output .= PHP_EOL . "<$key>" . $this->_renderArray($value) . "</$key>" . PHP_EOL;
            }
            else {
                $output .= PHP_EOL . "<$key>" . $this->_formatString($value) . "</$key>" . PHP_EOL;
            }
        }

        return $output;
    }

    /**
     *
     * format string
     *
     * @param mixed $input
     *
     * @return string
     */
    protected function _formatString($input)
    {
        if (is_array($input)) {
            return $input;
        }

        return strip_tags(
            str_ireplace(
                array('&'), array(' '), $input));
//        return strip_tags(
//            str_ireplace(
//                array('&amp;', '&#039;', '&quot;', '&lt;', '&gt;', '&nbsp;'), array('&', "'", '"', '<', '>', ' '), $string));

    }

    /**
     *
     * generate feed
     *
     * @return mixed
     */
    abstract function generateFeed();
} 