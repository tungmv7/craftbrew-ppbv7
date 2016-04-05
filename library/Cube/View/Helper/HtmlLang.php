<?php

/**
 *
 * Cube Framework $Id$ 70Qq6jAofmCtvh7HOxFRGYHABB/rfQorrqVAWebBNjo=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.2
 */
/**
 * add language to html lang tag
 * active locale is retrieved from translate adapter
 */

namespace Cube\View\Helper;

class HtmlLang extends AbstractHelper
{

    /**
     * default language
     */
    const DEFAULT_LANGUAGE = 'en';

    /**
     *
     * language string
     *
     * @var string
     */
    protected $_htmlLang = self::DEFAULT_LANGUAGE;

    /**
     *
     * head title method
     * will output the currently set locale after it has been properly formatted
     *
     * @return string
     */
    public function htmlLang()
    {
        $locale = $this->getTranslate()->getLocale();

        if (empty($locale)) {
            $locale = self::DEFAULT_LANGUAGE;
        }
        else {
            $locale = strtok($locale, '_');
        }

        return $locale;
    }

}

