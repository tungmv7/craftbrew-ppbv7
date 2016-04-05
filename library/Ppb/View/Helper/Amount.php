<?php

/**
 *
 * PHP Pro Bid $Id$ DoqV6Xuw8rNG5SdJkJyjJAlp1UN+ONvw4buD7qSDtr8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * amount display view helper class
 */

namespace Ppb\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Cube\Db\Table\Rowset,
    Ppb\Service\Table\Currencies as CurrenciesService;

class Amount extends AbstractHelper
{
    /**
     * the maximum amount allowed for a decimal input value
     */

    const MAX_AMOUNT = 99999999999;
    /**
     * default display format
     */

    const DEFAULT_FORMAT = '%s';

    /**
     *
     * currencies rowset
     *
     * @var \Cube\Db\Table\Rowset\AbstractRowset
     */
    protected $_currencies;

    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings;

    /**
     *
     * zero display value
     *
     * @var bool
     */
    protected $_zero = null;

    /**
     *
     * class constructor
     *
     * @param array $settings the settings array
     */
    public function __construct(array $settings)
    {
        $this->setSettings($settings);
    }

    /**
     *
     * set settings array
     *
     * @param array $settings
     *
     * @return \Ppb\View\Helper\Amount
     */
    public function setSettings(array $settings)
    {
        $this->_settings = $settings;

        return $this;
    }

    /**
     *
     * fetch currencies from table
     *
     * @param string $isoCode currency to fetch (by iso code)
     *
     * @return \Cube\Db\Table\Row\AbstractRow|null   selected or default currency row or null if requested currency cannot be found
     */
    public function getCurrency($isoCode = null)
    {
        if (!$this->_currencies instanceof Rowset) {
            $service = new CurrenciesService();
            $this->_currencies = $service->fetchAll();
        }

        if ($isoCode === null) {
            $isoCode = $this->_settings['currency'];
        }

        foreach ($this->_currencies as $currency) {
            if ($currency['iso_code'] == $isoCode) {
                return $currency;
            }
        }

        return null;
    }

    /**
     *
     * set zero value
     *
     * @param string $zero
     *
     * @return \Ppb\View\Helper\Amount
     */
    public function setZero($zero)
    {
        $this->_zero = $zero;

        return $this;
    }

    /**
     *
     * amount view helper
     *
     * @param float  $amount        the amount to be displayed
     * @param string $currency      the currency - default currency used if this is null
     * @param string $format        display format, used if custom outputs are needed
     *                              eg: (+%s)
     * @param bool   $overrideZero
     *
     * @return string|$this
     */
    public function amount($amount = null, $currency = null, $format = null, $overrideZero = false)
    {
        if ($amount === false) {
            return $this;
        }

        $translate = $this->getTranslate();

        if ($amount >= self::MAX_AMOUNT) {
            return $translate->_('Above');
        }

        if ($format === null) {
            $format = self::DEFAULT_FORMAT;
        }

        if ($amount == 0 && $overrideZero === false) {
            if ($this->_settings['display_free_fees']) {
                return sprintf($format, $translate->_('Free'));
            }

            return $translate->_($this->_zero);
        }

        $data = $this->getCurrency($currency);

        $symbol = $data['iso_code'];
        $spacing = ' ';
        if (!empty($data['symbol'])) {
            $symbol = $data['symbol'];
            $spacing = '';
        }

        switch ($this->_settings['currency_format']) {
            case '1':
                $amount = number_format($amount, $this->_settings['currency_decimals'], '.', ',');
                break;
            default:
                $amount = number_format($amount, $this->_settings['currency_decimals'], ',', '.');
                break;
        }

        switch ($this->_settings['currency_position']) {
            case '1':
                $output = $symbol . $spacing . $amount;
                break;
            default:
                $output = $amount . $spacing . $symbol;
                break;
        }


        return sprintf($format, $output);
    }

}