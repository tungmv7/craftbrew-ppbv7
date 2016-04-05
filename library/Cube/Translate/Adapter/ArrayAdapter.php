<?php

/**
 *
 * Cube Framework $Id$ Boj3xOLKEvBEjiBrYl13ljwjNUSGcVSGYv8qTpYGnss=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\Translate\Adapter;

use Cube\Config\AbstractConfig,
    Cube\Locale;

/**
 *
 * array translate adapter
 * will accept arrays as inputs
 *
 * Class ArrayAdapter
 *
 * @package Cube\Translate\Adapter
 */
class ArrayAdapter extends AbstractAdapter
{

    /**
     *
     * add translation to the adapter
     *
     * @param array $options
     *
     * @return $this
     * @throws \InvalidArgumentException
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

            if (file_exists($file) === false) {
                throw new \InvalidArgumentException(
                    sprintf("Add translation method error: The translation file '%s' could not be found.", $file));
            }

            if (Locale::isLocale($locale) === false) {
                throw new \InvalidArgumentException(
                    sprintf("Add translation method error: '%s' is an invalid locale.", $locale));
            }

            $this->_translate[$locale] = include($file);
        }

        return $this;
    }

} 