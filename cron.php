<?php
/**
 *
 * PHP Pro Bid $Id$ OklwQxJCGV8SI2F11n+Z3bN1H/vPzTdpy9+bwfs6nMI=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */

ini_set('display_errors', 0);
error_reporting(0);

define('APPLICATION_PATH', realpath(__DIR__));
set_include_path(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'library');

require_once 'Cube/Application.php';

$application = Cube\Application::init(include 'config/global.config.php');
$application->bootstrap();

$request = new \Cube\Controller\Request();
$command = $request->getParam('command', null);

$service = new \Ppb\Service\Cron();
$service->run($command);

