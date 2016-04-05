<?php
/**
 *
 * PHP Pro Bid $Id$ 08mUDxbECBwYpMu5lTHZooy3eAfQafkCRjG35WsUFS4=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.3
 */
/**
 * bad words filter
 */

namespace Ppb\Filter;

use Cube\Filter\AbstractFilter,
    Ppb\Service\Table\WordFilter as WordFilterService;

class BadWords extends AbstractFilter
{
    const REPLACEMENT = '#@$%';

    protected $_words = null;

    /**
     *
     * set words array
     *
     * @param array $words
     *
     * @return $this
     */
    public function setWords(array $words)
    {
        $this->_words = $words;

        return $this;
    }

    /**
     *
     * get words array, initialize if not set
     *
     * @return array
     */
    public function getWords()
    {
        if ($this->_words === null) {
            $service = new WordFilterService();
            $data = $service->fetchAll();

            $words = array();
            foreach ($data as $word) {
                $words[] = $word['word'];
            }

            $this->setWords($words);
        }

        return $this->_words;
    }

    /**
     *
     * clear words array
     *
     * @return $this
     */
    public function clearWords()
    {
        $this->_words = null;

        return $this;
    }

    /**
     *
     * replace all bad words found in the input with the standard replacement
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function filter($value)
    {
        $words = $this->getWords();

        return str_ireplace($words, self::REPLACEMENT, $value);
    }
} 