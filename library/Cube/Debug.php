<?php

/**
 *
 * Cube Framework $Id$ Ny2ZHOIPEJlmKPQJR/CEd+vWgMndEz5qCr1vt1y5Oas=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.6
 */

namespace Cube;

/**
 * debug and different usage statistics class class
 *
 * Class Debug
 *
 * @package Cube
 */
class Debug
{

    protected static $_memoryStart;
    protected static $_memoryEnd;
    protected static $_timeStart;
    protected static $_timeEnd;
    protected static $_sqlQueries = array();
    protected static $_sqlCount = 0;


    protected static $_cpuUsageStart;
    protected static $_cpuUsageEnd;
    protected static $_cpuTimeUsageStart;
    protected static $_cpuTimeUsageEnd;

    public static function setMemoryStart()
    {
        self::$_memoryStart = self::_getMemory();
    }

    public static function setMemoryEnd()
    {
        self::$_memoryEnd = self::_getMemory();
    }

    public static function setTimeStart()
    {
        self::$_timeStart = self::_getCurrentTime();
    }

    public static function setTimeEnd()
    {
        self::$_timeEnd = self::_getCurrentTime();
    }

    public static function addSqlCount()
    {
        self::$_sqlCount++;
    }

    public static function setCpuUsageStart()
    {
        self::$_cpuUsageStart = self::_getCpuUsage();
        self::$_cpuTimeUsageStart = microtime(true);
    }

    public static function setCpuUsageEnd()
    {
        self::$_cpuUsageEnd = self::_getCpuUsage();
        self::$_cpuTimeUsageEnd = microtime(true);
    }

    /**
     *
     * add sql query to the debug log
     *
     * @param string $query
     * @param null   $count
     */
    public static function addSqlQuery($query, $count = null)
    {
        if ($count === null) {
            $count = count(self::$_sqlQueries);
        }

        $time = self::_getCurrentTime();
        if (array_key_exists($count, self::$_sqlQueries)) {
            $time -= self::$_sqlQueries[$count]['time'];
        }

        self::$_sqlQueries[$count] = array(
            'query' => (string)$query,
            'time'  => $time,
        );
    }

    /**
     *
     * get current sql query count
     *
     * @return integer
     */
    public static function getSqlCount()
    {
        return self::$_sqlCount;
    }

    /**
     *
     * will return the memory usage in KB
     *
     * @return string
     */
    public static function getMemoryUsage()
    {
        self::setMemoryEnd();

        return number_format((self::$_memoryEnd - self::$_memoryStart) / 1024, 2);
    }

    /**
     *
     * will return the loading time in seconds with 3 decimals
     *
     * @return string
     */
    public static function getLoadingSpeed()
    {
        self::setTimeEnd();

        return number_format((self::$_timeEnd - self::$_timeStart), 3);
    }

    /**
     *
     * will return the sql queries executed
     *
     * @return integer
     */
    public static function getSqlQueries()
    {
        return self::$_sqlQueries;
    }

    /**
     *
     * will return the number of sql queries executed
     *
     * @return integer
     */
    public static function getCountSqlQueries()
    {
        return count(self::$_sqlQueries);
    }

    /**
     *
     * returns cpu usage
     *
     * @return float
     */
    public static function getCpuUsage()
    {
        self::setCpuUsageEnd();

        $time = (self::$_cpuTimeUsageEnd - self::$_cpuTimeUsageStart) * 1000000;

        if ($time > 0) {
            $usec = self::$_cpuUsageEnd - self::$_cpuUsageStart;

            return sprintf("%01.2f", ($usec / $time) * 100);
        }

        return 0;
    }

    /**
     *
     * sets a loading time variable
     *
     * @return integer
     */
    protected static function _getCurrentTime()
    {
        list($usec, $sec) = explode(" ", microtime());

        return ((float)$usec + (float)$sec);
    }

    /**
     *
     * sets a memory load variable
     *
     * @return integer
     */
    protected static function _getMemory()
    {
        return memory_get_usage();
    }

    /**
     *
     * get cpu usage
     *
     * @return float|null
     */
    protected static function _getCpuUsage()
    {
        if (function_exists('getrusage')) {
            $data = getrusage();

            return $data["ru_utime.tv_sec"] * 1e6 + $data["ru_utime.tv_usec"];
        }

        return 0;
    }

}

