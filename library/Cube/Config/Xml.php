<?php

/**
 *
 * Cube Framework $Id$ WNsNFVW6xk/JoqIiWwZhH65QxRxlXyOlD0Gz5ewcAYk=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * config xml generator class
 */

namespace Cube\Config;

class Xml extends AbstractConfig
{

    /**
     *
     * convert input into \SimpleXMLElement, then process the xml into an array
     *
     * @param mixed $data the input variable, it can be a path to an xml file, a string in xml format or an object of type \SimpleXMLElement
     *
     * @return $this
     */
    public function addData($data)
    {
        $xml  = $this->_processInput($data);
        $data = json_decode(json_encode((array)$xml), true);

        $this->_data = array_replace_recursive(
            array_merge_recursive($this->_data, $data), $data);

        return $this;
    }

    /**
     *
     * process input
     *
     * @param $input
     *
     * @return null|\SimpleXMLElement
     */
    protected function _processInput($input)
    {
        $xml = null;
        if ($input instanceof \SimpleXMLElement) {
            $xml = $input;
        } else if (file_exists($input)) {
            $xml = simplexml_load_file($input);
        } else {
            $xml = simplexml_load_string($input);
        }

        return $xml;
    }

}

