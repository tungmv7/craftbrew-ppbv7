<?php

/**
 *
 * Cube Framework $Id$ ud8pL4md/+RQ54VuntOSI5fU60v7lrcUFRk9V6NHHKY=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.6
 */

namespace Cube;

class Locale
{

    const LANG_TOKEN = 'LangToken';

    const DEFAULT_LOCALE = 'en';

    private static $_data = array(
        'Albanian (Albania)'               => 'sq_AL',
        'Albanian'                         => 'sq',
        'Arabic (Algeria)'                 => 'ar_DZ',
        'Arabic (Bahrain)'                 => 'ar_BH',
        'Arabic (Egypt)'                   => 'ar_EG',
        'Arabic (Iraq)'                    => 'ar_IQ',
        'Arabic (Jordan)'                  => 'ar_JO',
        'Arabic (Kuwait)'                  => 'ar_KW',
        'Arabic (Lebanon)'                 => 'ar_LB',
        'Arabic (Libya)'                   => 'ar_LY',
        'Arabic (Morocco)'                 => 'ar_MA',
        'Arabic (Oman)'                    => 'ar_OM',
        'Arabic (Qatar)'                   => 'ar_QA',
        'Arabic (Saudi Arabia)'            => 'ar_SA',
        'Arabic (Sudan)'                   => 'ar_SD',
        'Arabic (Syria)'                   => 'ar_SY',
        'Arabic (Tunisia)'                 => 'ar_TN',
        'Arabic (United Arab Emirates)'    => 'ar_AE',
        'Arabic (Yemen)'                   => 'ar_YE',
        'Arabic'                           => 'ar',
        'Belarusian (Belarus)'             => 'be_BY',
        'Belarusian'                       => 'be',
        'Bulgarian (Bulgaria)'             => 'bg_BG',
        'Bulgarian'                        => 'bg',
        'Catalan (Spain)'                  => 'ca_ES',
        'Catalan'                          => 'ca',
        'Chinese (China)'                  => 'zh_CN',
        'Chinese (Hong Kong)'              => 'zh_HK',
        'Chinese (Singapore)'              => 'zh_SG',
        'Chinese (Taiwan)'                 => 'zh_TW',
        'Chinese'                          => 'zh',
        'Croatian (Croatia)'               => 'hr_HR',
        'Croatian'                         => 'hr',
        'Czech (Czech Republic)'           => 'cs_CZ',
        'Czech'                            => 'cs',
        'Danish (Denmark)'                 => 'da_DK',
        'Danish'                           => 'da',
        'Dutch (Belgium)'                  => 'nl_BE',
        'Dutch (Netherlands)'              => 'nl_NL',
        'Dutch'                            => 'nl',
        'English (Australia)'              => 'en_AU',
        'English (Canada)'                 => 'en_CA',
        'English (India)'                  => 'en_IN',
        'English (Ireland)'                => 'en_IE',
        'English (Malta)'                  => 'en_MT',
        'English (New Zealand)'            => 'en_NZ',
        'English (Philippines)'            => 'en_PH',
        'English (Singapore)'              => 'en_SG',
        'English (South Africa)'           => 'en_ZA',
        'English (United Kingdom)'         => 'en_GB',
        'English (United States)'          => 'en_US',
        'English'                          => 'en',
        'Estonian (Estonia)'               => 'et_EE',
        'Estonian'                         => 'et',
        'Farsi (Iran)'                     => 'fa_IR',
        'Finnish (Finland)'                => 'fi_FI',
        'Finnish'                          => 'fi',
        'French (Belgium)'                 => 'fr_BE',
        'French (Canada)'                  => 'fr_CA',
        'French (France)'                  => 'fr_FR',
        'French (Luxembourg)'              => 'fr_LU',
        'French (Switzerland)'             => 'fr_CH',
        'French'                           => 'fr',
        'German (Austria)'                 => 'de_AT',
        'German (Germany)'                 => 'de_DE',
        'German (Luxembourg)'              => 'de_LU',
        'German (Switzerland)'             => 'de_CH',
        'German'                           => 'de',
        'Greek (Cyprus)'                   => 'el_CY',
        'Greek (Greece)'                   => 'el_GR',
        'Greek'                            => 'el',
        'Hebrew (Israel)'                  => 'iw_IL',
        'Hebrew'                           => 'iw',
        'Hindi (India)'                    => 'hi_IN',
        'Hungarian (Hungary)'              => 'hu_HU',
        'Hungarian'                        => 'hu',
        'Icelandic (Iceland)'              => 'is_IS',
        'Icelandic'                        => 'is',
        'Indonesian (Indonesia)'           => 'id_ID',
        'Indonesian'                       => 'id',
        'Irish (Ireland)'                  => 'ga_IE',
        'Irish'                            => 'ga',
        'Italian (Italy)'                  => 'it_IT',
        'Italian (Switzerland)'            => 'it_CH',
        'Italian'                          => 'it',
        'Japanese (Japan)'                 => 'ja_JP',
        'Japanese (Japan, JP)'             => 'ja_JP_JP',
        'Japanese'                         => 'ja',
        'Korean (South Korea)'             => 'ko_KR',
        'Korean'                           => 'ko',
        'Latvian (Latvia)'                 => 'lv_LV',
        'Latvian'                          => 'lv',
        'Lithuanian (Lithuania)'           => 'lt_LT',
        'Lithuanian'                       => 'lt',
        'Macedonian (Macedonia)'           => 'mk_MK',
        'Macedonian'                       => 'mk',
        'Malay (Malaysia)'                 => 'ms_MY',
        'Malay'                            => 'ms',
        'Maltese (Malta)'                  => 'mt_MT',
        'Maltese'                          => 'mt',
        'Norwegian (Norway)'               => 'no_NO',
        'Norwegian (Norway, Nynorsk)'      => 'no_NO_NY',
        'Norwegian'                        => 'no',
        'Polish (Poland)'                  => 'pl_PL',
        'Polish'                           => 'pl',
        'Portuguese (Brazil)'              => 'pt_BR',
        'Portuguese (Portugal)'            => 'pt_PT',
        'Portuguese'                       => 'pt',
        'Romanian (Romania)'               => 'ro_RO',
        'Romanian'                         => 'ro',
        'Russian (Russia)'                 => 'ru_RU',
        'Russian'                          => 'ru',
        'Kinyarwanda (Rwanda)'             => 'rw_RW',
        'Serbian (Bosnia and Herzegovina)' => 'sr_BA',
        'Serbian (Montenegro)'             => 'sr_ME',
        'Serbian (Serbia and Montenegro)'  => 'sr_CS',
        'Serbian (Serbia)'                 => 'sr_RS',
        'Serbian'                          => 'sr',
        'Slovak (Slovakia)'                => 'sk_SK',
        'Slovak'                           => 'sk',
        'Slovenian (Slovenia)'             => 'sl_SI',
        'Slovenian'                        => 'sl',
        'Spanish (Argentina)'              => 'es_AR',
        'Spanish (Bolivia)'                => 'es_BO',
        'Spanish (Chile)'                  => 'es_CL',
        'Spanish (Colombia)'               => 'es_CO',
        'Spanish (Costa Rica)'             => 'es_CR',
        'Spanish (Dominican Republic)'     => 'es_DO',
        'Spanish (Ecuador)'                => 'es_EC',
        'Spanish (El Salvador)'            => 'es_SV',
        'Spanish (Guatemala)'              => 'es_GT',
        'Spanish (Honduras)'               => 'es_HN',
        'Spanish (Mexico)'                 => 'es_MX',
        'Spanish (Nicaragua)'              => 'es_NI',
        'Spanish (Panama)'                 => 'es_PA',
        'Spanish (Paraguay)'               => 'es_PY',
        'Spanish (Peru)'                   => 'es_PE',
        'Spanish (Puerto Rico)'            => 'es_PR',
        'Spanish (Spain)'                  => 'es_ES',
        'Spanish (United States)'          => 'es_US',
        'Spanish (Uruguay)'                => 'es_UY',
        'Spanish (Venezuela)'              => 'es_VE',
        'Spanish'                          => 'es',
        'Swedish (Sweden)'                 => 'sv_SE',
        'Swedish'                          => 'sv',
        'Thai (Thailand)'                  => 'th_TH',
        'Thai (Thailand, TH)'              => 'th_TH_TH',
        'Thai'                             => 'th',
        'Turkish (Turkey)'                 => 'tr_TR',
        'Turkish'                          => 'tr',
        'Ukrainian (Ukraine)'              => 'uk_UA',
        'Ukrainian'                        => 'uk',
        'Vietnamese (Vietnam)'             => 'vi_VN',
        'Vietnamese'                       => 'vi',
    );

    /**
     *
     * locale value
     *
     * @var string
     */
    protected $_locale;

    /**
     *
     * class constructor
     *
     * @param string $locale
     */
    public function __construct($locale = null)
    {
        $this->setLocale($locale);
    }

    /**
     *
     * get active locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     *
     * set active locale
     *
     * @param string $locale
     *
     * @return \Cube\Locale
     */
    public function setLocale($locale)
    {
        if (array_search($locale, self::$_data) !== false) {
            $this->_locale = $locale;
        }
        else {
            $this->_locale = self::DEFAULT_LOCALE;
        }

        setlocale(LC_COLLATE, $this->_locale);

        if (!in_array($this->_locale, array('tr', 'tr_TR'))) {
            setlocale(LC_CTYPE, $this->_locale); // for some reason this gives error for the turkish locales
        }

        setlocale(LC_MONETARY, $this->_locale);
        setlocale(LC_TIME, $this->_locale);

        setlocale(LC_NUMERIC, self::DEFAULT_LOCALE); // numeric values are always displayed in en locale

        return $this;
    }

    /**
     *
     * get locale array
     *
     * @return array
     */
    public static function getData()
    {
        return self::$_data;
    }

    /**
     *
     * get the (name) key of a locale or false if not found
     *
     * @param string $locale
     *
     * @return string|false
     */
    public static function getLocaleKey($locale)
    {
        return array_search($locale, self::$_data);
    }

    /**
     *
     * get the name (key) of a locale, or return the initial value if not found
     *
     * @param string $locale
     *
     * @return string
     */
    public static function getLocaleName($locale)
    {
        return (($output = self::getLocaleKey($locale)) === false) ? $locale : $output;
    }

    /**
     *
     * check if a locale variable exists in the locales array and return true if it is, false otherwise
     *
     * @param string $locale
     *
     * @return bool
     */
    public static function isLocale($locale)
    {
        if (array_search($locale, self::$_data) !== false) {
            return true;
        }

        return false;
    }

}

