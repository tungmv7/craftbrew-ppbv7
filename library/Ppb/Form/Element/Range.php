<?php

/**
 *
 * PHP Pro Bid $Id$ 5o6UBBTBMMrTlUXgqHFcj0Vkuyu7nQr8Ey2dylu8/Uw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * range form element
 */

namespace Ppb\Form\Element;

use Cube\Form\Element;

class Range extends Element
{

    const RANGE_FROM = '0';
    const RANGE_TO = '1';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'range';

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct('text', $name);
        $this->setMultiple(true);
    }


    /**
     *
     * return the value(s) of the element, either the element's data or default value(s)
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getValue($key = null)
    {
        $value = parent::getValue();

        if ($key !== null) {
            if (array_key_exists($key, (array)$value)) {
                return $value[$key];
            }
            else {
                return null;
            }
        }

        return $value;
    }

    /**
     *
     * render composite element
     *
     * @return string
     */
    public function render()
    {
        return $this->getPrefix() . ' '
        . '<input type="' . $this->_type . '" '
        . 'name="' . $this->_name . '[' . self::RANGE_FROM . ']' . '" '
        . $this->renderAttributes()
        . 'value="' . $this->getValue(self::RANGE_FROM) . '" '
        . $this->_endTag . ' '
        . $this->getSuffix()
        . ' - '
        . $this->getPrefix() . ' '
        . '<input type="' . $this->_type . '" '
        . 'name="' . $this->_name . '[' . self::RANGE_TO . ']' . '" '
        . $this->renderAttributes()
        . 'value="' . $this->getValue(self::RANGE_TO) . '" '
        . $this->_endTag . ' '
        . $this->getSuffix();
    }

}

