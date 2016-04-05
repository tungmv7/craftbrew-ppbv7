<?php

/**
 *
 * Cube Framework $Id$ wi/Dqv9KzYan7I1PS3x/ZTn1o/Cg2fROPxfk2upq8h8=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * abstract db table rowset class
 */

namespace Cube\Db\Table\Rowset;

use Cube\Db\Table\AbstractTable,
    Cube\Controller\Front,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter,
    Cube\Translate;

class AbstractRowset implements \SeekableIterator, \Countable, \ArrayAccess
{

    /**
     *
     * data for each row
     *
     * @var array
     */
    protected $_data = array();

    /**
     *
     * table class the row belongs to
     *
     * @var \Cube\Db\Table\AbstractTable
     */
    protected $_table = null;

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    /**
     *
     * iterator pointer
     *
     * @var integer
     */
    protected $_pointer = 0;

    /**
     *
     * number of rows in the rowset
     *
     * @var integer
     */
    protected $_count;


    /**
     * array of \Cube\Db\Table\Row\AbstractRow objects
     *
     * @var array
     */
    protected $_rows = array();

    /**
     *
     * row object class
     *
     * @var string
     */
    protected $_rowClass = '\Cube\Db\Table\Row';

    /**
     *
     * class constructor
     */
    public function __construct(array $data = array())
    {
        if (isset($data['table']) && $data['table'] instanceof AbstractTable) {
            $this->_table = $data['table'];
        }
        else {
            throw new \InvalidArgumentException("The 'table' key must be set when creating a Rowset object");
        }

        if (isset($data['data'])) {
            if (!is_array($data['data'])) {
                throw new \InvalidArgumentException('The "data" key must be an array');
            }

            $this->_data = $data['data'];
            $this->_count = count($this->_data);
        }

        if (isset($data['rowClass'])) {
            $this->_rowClass = $data['rowClass'];
        }
    }

    /**
     *
     * get table object
     *
     * @return \Cube\Db\Table\AbstractTable
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     *
     * set translate adapter
     *
     * @param \Cube\Translate\Adapter\AbstractAdapter $translate
     *
     * @return $this
     */
    public function setTranslate(TranslateAdapter $translate)
    {
        $this->_translate = $translate;

        return $this;
    }

    /**
     *
     * get translate adapter
     *
     * @return \Cube\Translate\Adapter\AbstractAdapter
     */
    public function getTranslate()
    {
        if (!$this->_translate instanceof TranslateAdapter) {
            $translate = Front::getInstance()->getBootstrap()->getResource('translate');
            if ($translate instanceof Translate) {
                $this->setTranslate(
                    $translate->getAdapter());
            }
        }

        return $this->_translate;
    }

    /**
     * Returns a \Cube\Db\Table\Row\AbstractRow from a known position into the Iterator
     *
     * @param int  $position the position of the row expected
     * @param bool $seek     whether or not seek the iterator to that position after
     *
     * @return \Cube\Db\Table\Row\AbstractRow|null
     */
    public function getRow($position, $seek = false)
    {
        try {
            $row = $this->_loadAndReturnRow($position);
        } catch (\Exception $e) {
            return null;
        }

        if ($seek == true) {
            $this->seek($position);
        }

        return $row;
    }

    /**
     *
     * proxy for the save() method for each row in the rowset
     *
     * @param array $data   partial data to be saved
     *                      the complete row is saved if this parameter is null
     *
     * @return $this
     */
    public function save(array $data = null)
    {
        /** @var \Cube\Db\Table\Row\AbstractRow $row */
        foreach ($this as $row) {
            $row->save($data);
        }

        return $this;
    }

    /**
     *
     * delete all rows from the corresponding rowset individually
     *
     * @return $this
     */
    public function delete()
    {
        /** @var \Cube\Db\Table\Row\AbstractRow $row */
        foreach ($this as $row) {
            $row->delete();
        }

        return $this;
    }

    /**
     *
     * returns all data as an array.
     *
     * @return array
     */
    public function toArray()
    {
        /** @var \Cube\Db\Table\Row\AbstractRow $row */
        foreach ($this->_rows as $i => $row) {
            $this->_data[$i] = $row->toArray();
        }

        return $this->_data;
    }

    /*
     * methods needed to implement the \SeekableIterator, \Countable and \ArrayAccess interfaces
     */

    /**
     *
     * check whether an offset exists
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[(int)$offset]);
    }

    /**
     *
     * offset to retrieve
     *
     * @param mixed $offset
     *
     * @return mixed|null
     * @throws \RuntimeException
     */
    public function offsetGet($offset)
    {
        $offset = (int)$offset;
        if ($offset < 0 || $offset >= $this->_count) {
            throw new \RuntimeException("Illegal index $offset");
        }
        $this->_pointer = $offset;

        return $this->current();
    }

    /**
     *
     * offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->_data[(int)$offset] = $value;
    }

    /**
     *
     * unset offset
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->_data[(int)$offset]);
    }

    /**
     *
     * count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return $this->_count;
    }

    /**
     *
     * return the current element
     *
     * @return mixed|null
     */
    public function current()
    {
        if ($this->valid() === false) {
            return null;
        }

        // return the row object
        return $this->_loadAndReturnRow($this->_pointer);
    }

    /**
     *
     * return the key of the current element
     *
     * @return int|mixed
     */
    public function key()
    {
        return $this->_pointer;
    }

    public function next()
    {
        ++$this->_pointer;
    }

    /**
     *
     * rewind the Iterator to the first element
     *
     * @return $this|void
     */
    public function rewind()
    {
        $this->_pointer = 0;

        return $this;
    }

    /**
     *
     * seek to a position
     *
     * @param int $position
     *
     * @return $this|void
     * @throws \RuntimeException
     */
    public function seek($position)
    {
        $position = (int)$position;
        if ($position < 0 || $position >= $this->_count) {
            throw new \RuntimeException(
                sprintf("Illegal index %s", $position));
        }
        $this->_pointer = $position;

        return $this;
    }

    /**
     *
     * checks if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->_pointer >= 0 && $this->_pointer < $this->_count;
    }

    /**
     *
     * return the object row from a selected position
     *
     * @param $position
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function _loadAndReturnRow($position)
    {
        if (!isset($this->_data[$position])) {
            throw new \InvalidArgumentException("Data for provided position does not exist");
        }

        if (empty($this->_rows[$position])) {
            $this->_rows[$position] = new $this->_rowClass(
                array(
                    'table' => $this->_table,
                    'data'  => $this->_data[$position],
                )
            );
        }

        // return the row object
        return $this->_rows[$position];
    }

}

