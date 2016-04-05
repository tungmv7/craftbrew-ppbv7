<?php

/**
 *
 * Cube Framework $Id$ q5xlbzX6/4egdQcC+miBeqADTDX747hVMaBJM8lmHBQ=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 */

namespace Cube\Application\Resource;

/**
 * application resource interface
 *
 * Interface ResourceInterface
 *
 * @package Cube\Application\Resource
 */
interface ResourceInterface
{

    public function getOptions();

    /**
     *
     * set options array
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options);

    /**
     *
     * get a key from the options array
     *
     * @param string $key
     */
    public function getOption($key);

    /**
     *
     * set or unset a key in the options array
     *
     * @param string     $key
     * @param mixed|null $value
     */
    public function setOption($key, $value = null);

    public function init();
}

