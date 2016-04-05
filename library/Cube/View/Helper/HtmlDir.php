<?php

/**
 *
 * Cube Framework $Id$ FTV1VE4YKTFlFqq/78Z/JoUGSNILdJUqzTlW4wE1HDc=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * add direction to html dir tag
 * active locale is retrieved from translate adapter
 */

namespace Cube\View\Helper;

class HtmlDir extends AbstractHelper
{

    /**
     * directions
     */
    const LTR = 'ltr';
    const RTL = 'rtl';

    /**
     * rtl locales
     */
    protected $_rtlLocales = array(
        'Arabic (Algeria)'              => 'ar_DZ',
        'Arabic (Bahrain)'              => 'ar_BH',
        'Arabic (Egypt)'                => 'ar_EG',
        'Arabic (Iraq)'                 => 'ar_IQ',
        'Arabic (Jordan)'               => 'ar_JO',
        'Arabic (Kuwait)'               => 'ar_KW',
        'Arabic (Lebanon)'              => 'ar_LB',
        'Arabic (Libya)'                => 'ar_LY',
        'Arabic (Morocco)'              => 'ar_MA',
        'Arabic (Oman)'                 => 'ar_OM',
        'Arabic (Qatar)'                => 'ar_QA',
        'Arabic (Saudi Arabia)'         => 'ar_SA',
        'Arabic (Sudan)'                => 'ar_SD',
        'Arabic (Syria)'                => 'ar_SY',
        'Arabic (Tunisia)'              => 'ar_TN',
        'Arabic (United Arab Emirates)' => 'ar_AE',
        'Arabic (Yemen)'                => 'ar_YE',
        'Arabic'                        => 'ar',
        'Farsi (Iran)'                  => 'fa_IR',
        'Hebrew (Israel)'               => 'iw_IL',
        'Hebrew'                        => 'iw',
    );

    /**
     *
     * html dir method
     *
     * @return string
     */
    public function htmlDir()
    {
        $locale = $this->getTranslate()->getLocale();

        if (!empty($locale)) {
            if (in_array($locale, $this->_rtlLocales)) {
                return self::RTL;
            }
        }

        return self::LTR;
    }

}

