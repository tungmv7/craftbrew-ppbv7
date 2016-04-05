<?php

/**
 * 
 * PHP Pro Bid $Id$ qdcav3PgLFcU5Wdn2n9Z7UxZGdMDD/lGosur28fDQFM=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */

/**
 * base form element class
 * 
 */
namespace Ppb\Form;

use Cube\Form\Element as ElementBase;

class Element extends ElementBase
{

    /**
     * 
     * method to create a new form element
     * 
     * @param string $element       the element type
     * @param string $name          the name of the element
     * @return \Cube\Form\Element    returns a form element object
     */
    protected function _createElement($element, $name)
    {
        $elementClass = '\\Cube\\Form\\Element\\' . ucfirst($element);

        if (class_exists($element)) {
            return new $element($name);
        }
        else if (class_exists($elementClass)) {
            return new $elementClass($name);
        }
        else {
            return new Element($element, $name);
        }
    }
}

