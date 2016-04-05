<?php

/**
 *
 * Ported from Zend Framework
 *
 * @copyright  Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Cube\Translate\Adapter;

use Cube\Config\AbstractConfig,
    Cube\Locale;

/**
 * gettext translate adapter
 *
 * Class Gettext
 *
 * @package Cube\Translate\Adapter
 */
class Gettext extends AbstractAdapter
{
    // Internal variables
    private $_bigEndian = false;
    private $_file = false;

    /**
     * Read values from the MO file
     *
     * @param  string $bytes
     *
     * @return array
     */
    private function _readMOData($bytes)
    {
        if ($this->_bigEndian === false) {
            return unpack('V' . $bytes, fread($this->_file, 4 * $bytes));
        }
        else {
            return unpack('N' . $bytes, fread($this->_file, 4 * $bytes));
        }
    }

    /**
     *
     * add new translation in the adapter
     *
     * @param array $options
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function addTranslation($options = array())
    {
        if (!is_array($options) && !($options instanceof AbstractConfig)) {
            throw new \InvalidArgumentException("The translation object requires an
                array or an object of type \Cube\ConfigAbstract.");
        }
        else {
            if ($options instanceof AbstractConfig) {
                $options = $options->getData();
            }

            $file = (isset($options['file'])) ? $options['file'] : null;
            $locale = (isset($options['locale'])) ? $options['locale'] : null;

            $this->_file = @fopen($file, 'rb');

            if (!$this->_file) {
                throw new \RuntimeException(
                    sprintf("Error opening translation file '%s'.", $file));
            }

            if (@filesize($file) < 10) {
                @fclose($this->_file);
                throw new \RuntimeException(
                    sprintf("%s is not a gettext file.", $file));
            }

            if (Locale::isLocale($locale) === false) {
                throw new \InvalidArgumentException(
                    sprintf("Add translation method error: '%s' is an invalid locale.", $locale));
            }

            $this->_file = fopen($file, 'rb');

            // get Endian
            $input = $this->_readMOData(1);
            if (strtolower(substr(dechex($input[1]), -8)) == "950412de") {
                $this->_bigEndian = false;
            }
            else if (strtolower(substr(dechex($input[1]), -8)) == "de120495") {
                $this->_bigEndian = true;
            }
            else {
                @fclose($this->_file);
                throw new \RuntimeException(
                    sprintf("%s is not a gettext file.", $file));
            }

            // read revision - not supported for now
            $input = $this->_readMOData(1);

            // number of bytes
            $input = $this->_readMOData(1);
            $total = $input[1];

            // number of original strings
            $input = $this->_readMOData(1);
            $OOffset = $input[1];

            // number of translation strings
            $input = $this->_readMOData(1);
            $TOffset = $input[1];

            // fill the original table
            fseek($this->_file, $OOffset);
            $origtemp = $this->_readMOData(2 * $total);
            fseek($this->_file, $TOffset);
            $transtemp = $this->_readMOData(2 * $total);

            for ($count = 0; $count < $total; ++$count) {
                if ($origtemp[$count * 2 + 1] != 0) {
                    fseek($this->_file, $origtemp[$count * 2 + 2]);
                    $original = @fread($this->_file, $origtemp[$count * 2 + 1]);
                    $original = explode("\0", $original);
                }
                else {
                    $original[0] = '';
                }

                if ($transtemp[$count * 2 + 1] != 0) {
                    fseek($this->_file, $transtemp[$count * 2 + 2]);
                    $translate = fread($this->_file, $transtemp[$count * 2 + 1]);
                    $translate = explode("\0", $translate);
                    if ((count($original) > 1)) {
                        $this->_translate[$locale][$original[0]] = $translate;
                        array_shift($original);
                        foreach ($original as $orig) {
                            $this->_translate[$locale][$orig] = '';
                        }
                    }
                    else {
                        $this->_translate[$locale][$original[0]] = $translate[0];
                    }
                }
            }

            @fclose($this->_file);
        }

        return $this;
    }

} 