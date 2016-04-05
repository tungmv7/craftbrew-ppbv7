<?php

/**
 *
 * Cube Framework $Id$ p87yNxHGtRjVpHSGsMMJ3wfnw5l6u6j1z9OBfqIWtbA=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * composite translate adapter
 * will combine the sentences from multiple adapters, based on the data inserted
 */

namespace Ppb\Translate\Adapter;

use Cube\Translate\Adapter,
    Cube\Config\AbstractConfig,
    Cube\Locale;

class Composite extends Adapter\AbstractAdapter
{

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

            if (!isset($options['path'])) {
                throw new \InvalidArgumentException("The 'path' key must be set.");
            }

            $locale = (isset($options['locale'])) ? $options['locale'] : null;

            if (Locale::isLocale($locale) === false) {
                throw new \InvalidArgumentException(
                    sprintf("Add translation method error: '%s' is an invalid locale.", $locale));
            }

            if (!array_key_exists($locale, $this->_translate)) {
                $this->_translate[$locale] = array();
            }

            if (!empty($options['sources'])) {
                foreach ($options['sources'] as $source) {
                    if (!isset($source['adapter'])) {
                        throw new \InvalidArgumentException("The 'adapter' sub-key must be set.");
                    }
                    if (!isset($source['extension'])) {
                        throw new \InvalidArgumentException("The 'extension' sub-key must be set.");
                    }

                    $translateAdapter = $source['adapter'];
                    if (!class_exists($translateAdapter)) {
                        throw new \InvalidArgumentException(
                            sprintf("Class %s doesn't exist", $translateAdapter));
                    }

                    /** @var \Cube\Translate\Adapter\AbstractAdapter $adapter */
                    $adapter = new $translateAdapter(array(
                        'file'   => $options['path'] . '.' . $source['extension'],
                        'locale' => $locale,
                    ));

                    $translation = $adapter->getTranslate();

                    foreach ($translation as $locale => $sentences) {
                        $this->_translate[$locale] = array_merge($this->_translate[$locale], $sentences);
                    }
                }
            }
        }

        return $this;
    }
}