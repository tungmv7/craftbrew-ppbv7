<?php

/**
 *
 * PHP Pro Bid $Id$ 97QB+Wzc9pyeB8JPMVZPsFIQ3Gzf9ZaAZyIbiPYglKk=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * display price pseudo form element
 */

namespace Ppb\Form\Element;

use Cube\Form\Element\Hidden,
    Cube\Controller\Front;

class PriceDescription extends Hidden
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
        $currency = $this->getPrefix();

        $view = Front::getInstance()->getBootstrap()->getResource('view');

        return $view->amount($this->getValue(), $currency) . parent::render();
    }

}
