<?php

/**
 *
 * Cube Framework $Id$ w7OwKEeshGNLETk6orNLQ+Px1KqBpnpcaWFj4X7v61s=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * crypt class
 */

namespace Cube;

class Crypt
{

    /**
     *
     * encryption key
     *
     * @var string
     */
    protected $_key;

    /**
     * One of the MCRYPT_cipher name constants, or the name of the algorithm as string.
     *
     * @var string
     */
    protected $_cipher = MCRYPT_RIJNDAEL_256;

    /**
     * One of the MCRYPT_MODE_modename constants,
     * or one of the following strings: "ecb", "cbc", "cfb", "ofb", "nofb" or "stream".
     *
     * @var string
     */
    protected $_mode = MCRYPT_MODE_ECB;

    /**
     *
     * set encryption key
     *
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->_key = hash('sha256', $key, true);

        return $this;
    }

    /**
     *
     * get encryption key
     *
     * @throws \RuntimeException
     * @return string
     */
    public function getKey()
    {
        if (empty($this->_key)) {
            throw new \RuntimeException("The encryption key has not been set.");
        }

        return $this->_key;
    }

    /**
     *
     * set mcrypt cipher
     *
     * @param string $cipher
     * @return $this
     */
    public function setCipher($cipher)
    {
        $this->_cipher = $cipher;

        return $this;
    }

    /**
     *
     * get mcrypt cipher
     *
     * @return string
     */
    public function getCipher()
    {
        return $this->_cipher;
    }

    /**
     *
     * set mcrypt mode
     *
     * @param string $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;
    }

    /**
     *
     * get mcrypt mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->_mode;
    }


    /**
     *
     * encrypt a string
     *
     * @param $input
     * @return string
     */
    function encrypt($input)
    {
        return base64_encode(mcrypt_encrypt($this->getCipher(), $this->getKey(), $input, $this->getMode()));
    }

    /**
     *
     * decrypt an encrypted string
     *
     * @param $input
     * @return string
     */
    function decrypt($input)
    {
        return trim(mcrypt_decrypt($this->getCipher(), $this->getKey(), base64_decode($input), $this->getMode()));
    }


}

