<?php

/**
 *
 * Cube Framework $Id$ fCbqC7GsljCTuEgCwul0SEIWM2ycU/i0+ANFfhNByZ8=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.3
 */
/**
 * identical values validator class
 */

namespace Cube\Validate;

class Identical extends AbstractValidate
{

    protected $_message = "'%s' and '%name%' do not match.";

    /**
     *
     * check for strict values
     *
     * @var bool
     */
    private $_strict = true;

    /**
     *
     * variable name
     *
     * @var array
     */
    private $_variableName;

    /**
     *
     * variable value
     *
     * @var mixed
     */
    private $_variableValue;

    /**
     *
     * class constructor
     *
     * initialize the variable name and value plus if the matching is strict or not
     *
     * @param array $data       data[0] -> variable name;
     *                          data[1] -> variable value;
     *                          data[2] -> strict comparison
     */
    public function __construct(array $data = null)
    {
        $this->setVariableName($data[0])
            ->setVariableValue($data[1])
            ->setStrict($data[2]);
    }

    /**
     *
     * get strict value
     *
     * @return bool
     */
    public function getStrict()
    {
        return $this->_strict;
    }

    /**
     *
     * set strict value
     *
     * @param bool $strict
     *
     * @return \Cube\Validate\Identical
     */
    public function setStrict($strict = true)
    {
        $this->_strict = $strict;

        return $this;
    }

    /**
     *
     * get variable name
     *
     * @return string
     */
    public function getVariableName()
    {
        return $this->_variableName;
    }

    /**
     *
     * set variable name
     *
     * @param string $variableName
     *
     * @return \Cube\Validate\Identical
     */
    public function setVariableName($variableName)
    {
        $this->_variableName = $variableName;

        return $this;
    }

    /**
     *
     * get variable value
     *
     * @return mixed
     */
    public function getVariableValue()
    {
        return $this->_variableValue;
    }

    /**
     *
     * set variable value (can be a string, number, bool, array, object etc)
     *
     * @param mixed $variableValue
     *
     * @return \Cube\Validate\Identical
     */
    public function setVariableValue($variableValue)
    {
        $this->_variableValue = $variableValue;

        return $this;
    }

    /**
     *
     * checks if the variable contains an alphanumeric value
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $this->setMessage(
            str_replace('%name%', $this->_variableName, $this->getMessage()));

        if (($this->_strict && ($this->_value !== $this->_variableValue)) ||
            (!$this->_strict && ($this->_value != $this->_variableValue))
        ) {
            return false;
        }

        return true;
    }

}

