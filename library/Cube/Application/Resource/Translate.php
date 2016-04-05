<?php

/**
 *
 * Cube Framework $Id$ 87DIJGWQtasYzOzaac0Fsiajbvh7Eu9OC2YL1sztp58=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * translate resource management class
 * creates the translate object and adds the adapter
 * only accepts a single adapter
 */

namespace Cube\Application\Resource;

use Cube\Translate as TranslateObject;

class Translate extends AbstractResource
{

    /**
     *
     * @var \Cube\Translate;
     */
    protected $_translate;

    /**
     *
     * initialize translate object
     *
     * @throws \RuntimeException
     * @return \Cube\Translate
     */
    public function init()
    {
        if (!($this->_translate instanceof TranslateObject)) {
            if (!array_key_exists('translate', $this->_options)) {
                throw new \RuntimeException("The 'translate' key in the configuration array must be set.");
            }


            $this->_translate = new TranslateObject();

            if (!array_key_exists('adapter', $this->_options['translate']) ||
                !array_key_exists('translations', $this->_options['translate'])
            ) {
                throw new \RuntimeException("The 'adapter' and 'translations' keys need to be set when configuring the translate object.");
            }

            $adapterClass = $this->_options['translate']['adapter'];

            if (!class_exists($adapterClass)) {
                throw new \RuntimeException(
                    sprintf("Class %s doesn't exist", $adapterClass));
            }

            /** @var \Cube\Translate\Adapter\AbstractAdapter $adapter */
            $adapter = new $adapterClass();

            foreach ($this->_options['translate']['translations'] as $translation) {
                $adapter->addTranslation($translation);
            }
            $this->_translate->setAdapter($adapter);

        }

        return $this->_translate;
    }

}