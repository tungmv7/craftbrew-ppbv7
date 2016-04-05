<?php

/**
 *
 * Cube Framework $Id$ 87swNrGpcDHG6wb37x5JfZnbl3x+TiOLTGStWPkQHe8=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.6
 */

namespace Cube\View\Helper;

use Cube\Validate\AbstractValidate;

class Validate extends AbstractHelper
{

    /**
     * validator object
     *
     * @var \Cube\Validate\AbstractValidate
     */
    protected $_validator;

    /**
     *
     * get validator
     *
     * @return \Cube\Validate\AbstractValidate
     *
     * @throws \InvalidArgumentException
     */
    public function getValidator()
    {
        if (!$this->_validator instanceof AbstractValidate) {
            throw new \InvalidArgumentException("A validator has not been initialized.");
        }

        return $this->_validator;
    }

    /**
     *
     * set validator object
     *
     * @param \Cube\Validate\AbstractValidate $validator
     *
     * @return $this
     */
    public function setValidator(AbstractValidate $validator)
    {
        $this->_validator = $validator;

        return $this;
    }


    /**
     *
     * direct method, callable from within the view to return the helper
     *
     * @return $this
     */
    public function validate(AbstractValidate $validator = null)
    {
        if ($validator instanceof AbstractValidate) {
            $this->setValidator($validator);
        }

        return $this;
    }

    /**
     *
     * call validator is valid method
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $validator = $this->getValidator();
        $validator->setValue($value);

        return @$validator->isValid();
    }


    /**
     *
     * call magic method, calls validators and runs them directly
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return bool
     */
    public function __call($name, $arguments)
    {
        $className = '\\Cube\\Validate\\' . ucfirst($name);

        /** @var \Cube\Validate\AbstractValidate $validator */
        if (class_exists($className)) {
            $this->setValidator(
                new $className());
        }

        if (count($arguments) == 1) {
            $arguments = $arguments[0];
        }

        return $this->isValid($arguments);
    }
}

