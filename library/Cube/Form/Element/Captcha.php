<?php

/**
 * 
 * Cube Framework $Id$ 0ODEEX9wMJbafm7LFeIMBY5lYC75ubCX4eimHkILVos= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * creates a captcha element
 * 
 * TODO - refactor after creating thumbnail class
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Captcha extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     * 
     * @var string
     */
    protected $_element = 'captcha';

    /**
     *
     * the font that will be used
     * 
     * @var string
     */
    protected $_font;

    /**
     *
     * the width of the image 
     * 
     * @var integer
     */
    protected $_width = 220;

    /**
     *
     * the height of the image
     * 
     * @var integer
     */
    protected $_height = 50;

    /**
     *
     * the length of the captcha code
     * 
     * @var integer
     */
    protected $_length = 5;

    /**
     *
     * the size of the font
     * 
     * @var integer
     */
    protected $_fontSize = 32;

    /**
     *
     * the number of pixels added as noise on the image
     * 
     * @var integer
     */
    protected $_pixels = 50;

    /**
     *
     * the number of circles added as noise on the image
     * 
     * @var integer
     */
    protected $_circles = 5;

    /**
     *
     * the captcha code generated
     * 
     * @var string
     */
    protected $_code;

    /**
     *
     * the image that will be output
     * 
     * @var resource
     */
    protected $_image;

    /**
     *
     * the url of the image that will be generated
     * 
     * @var string
     */
    public $_captchaImage;

    /**
     * 
     * class constructor
     * 
     * @param string $name  the name of the form field
     * @param int $length   the length of the captcha in characters
     * @param int $width    the width of the captcha image
     * @param int $height   the height of the captcha image
     */
    public function __construct($name = 'captcha', $length = null, $width = null, $height = null)
    {
        parent::__construct($this->_type, $name);

        $this->_captchaImage = 'thumbnail.php?option=captcha&name=' . $this->_name;
        $this->addValidator('Captcha');

        if ($length !== null) {
            $this->_length = intval($length);
        }

        if ($width !== null) {
            $this->_width = intval($width);
        }

        if ($height !== null) {
            $this->_height = intval($height);
        }

        $this->_font = __DIR__ . '/../../../../fonts/Candal.ttf';
    }

    /**
     * 
     * create the captcha code and save it in a session variable
     * 
     * @return string
     */
    public function setCode()
    {
        srand();

        $this->_code = substr(md5(rand(2, 9999999999)), 0, $this->_length);

        $session = new PPS_Session();
        $session->set($this->_name, $this->_code);

        return $this->_code;
    }

    /**
     * 
     * get the captcha code
     * 
     * @return string
     */
    public function getCode()
    {
        if (!$this->_code) {
            $this->setCode();
        }

        return $this->_code;
    }

    /**
     * 
     * create the captcha image
     */
    public function createImage()
    {
        $this->_image = imagecreate($this->_width, $this->_height);

        $white = imagecolorallocate($this->_image, 255, 255, 255);
        $grey = imagecolorallocate($this->_image, 180, 180, 180);
        $black = imagecolorallocate($this->_image, 0, 0, 0);

        $code = $this->getCode();

        $height = $this->_height - (($this->_height - $this->_fontSize) / 2);

        // Add some shadow to the text
        imagettftext($this->_image, $this->_fontSize, 0, 41, ($height - 1), $grey, $this->_font, $this->_code);
        // Add the text
        imagettftext($this->_image, $this->_fontSize, 0, 40, $height, $black, $this->_font, $this->_code);

        srand();
        // add noise (pixels)
        for ($i = 1; $i <= $this->_pixels; $i++) {
            imagesetpixel($this->_image, rand(0, $this->_width), rand(0, $this->_height), $black);
        }
        // add noise (circles)
        for ($i = 1; $i <= $this->_circles; $i++) {
            imageellipse($this->_image, rand(0, $this->_width), rand(0, $this->_height), rand(60, 150), rand(60, 150), $black);
        }

        header("Content-Type: image/png");
        imagepng($this->_image);

        imagedestroy($this->_image);
    }

    public function render()
    {
        return '<input type="text" name="' . $this->_name . '" '
                . $this->renderAttributes()
                . $this->_endTag;
    }

}

