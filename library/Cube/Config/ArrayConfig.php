<?php

/**
 *
 * Cube Framework $Id$ VQOpwb8QfsPU+M1xNSZxmjShFSozfsJl9GQs/FAVVKA=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * array config object creator class
 */

namespace Cube\Config;

class ArrayConfig extends AbstractConfig
{
    /**
     *
     * class constructor
     *
     * load file and set the data in the container
     *
     * @param string $data
     * @param string $node
     */
    public function __construct($data = null, $node = null)
    {
        if (file_exists($data)) {
            $data = include $data;
        }

        parent::__construct($data, $node);
    }

    /**
     *
     * add data
     *
     * @param array $data
     *
     * @return $this
     */
    public function addData($data)
    {
        $this->_data = array_replace_recursive(
            array_merge_recursive($this->_data, $data), $data);

        return $this;
    }
}

