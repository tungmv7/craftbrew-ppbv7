<?php

/**
 *
 * Cube Framework $Id$ 87DIJGWQtasYzOzaac0Fsiajbvh7Eu9OC2YL1sztp58=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * translate class
 * the translation adapter will only translate sentences if a locale is set
 */

namespace Cube;

class Translate
{

    /**
     *
     * translation adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_adapter;

    /**
     *
     * get translation adapter
     *
     * @return \Cube\Translate\Adapter\AbstractAdapter
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     *
     * set the translation adapter
     *
     * @param \Cube\Translate\Adapter\AbstractAdapter $adapter
     *
     * @return \Cube\Translate
     */
    public function setAdapter(Translate\Adapter\AbstractAdapter $adapter)
    {

        $this->_adapter = $adapter;

        return $this;
    }

}

