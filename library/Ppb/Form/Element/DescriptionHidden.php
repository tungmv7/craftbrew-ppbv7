<?php

/**
 *
 * PHP Pro Bid $Id$ mk8nwVu2wZ2gpZdlnXNhHwsyQRx/WAMm7+31q61xtxI=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * description field with hidden element attached
 */

namespace Ppb\Form\Element;

use Cube\Form\Element\Hidden;

class DescriptionHidden extends Hidden
{

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->setHidden(false);
    }

    /**
     *
     * render pseudo element
     *
     * @return string
     */
    public function render()
    {
        return $this->getPrefix() . ' '
        . $this->getValue() . ' '
        . $this->getSuffix()
        . parent::render();
    }

}
