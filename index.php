<?php
/**
 *
 * PHP Pro Bid $Id$ llSNAsc+YJssRp1XrzpAewz5vril/sRCgQCdUXZ25Rw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('APPLICATION_PATH', realpath(__DIR__));

set_include_path(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'library');

require_once 'Cube/Application.php';

$application = Cube\Application::init(include 'config/global.config.php');
$application->bootstrap()
        ->run();
