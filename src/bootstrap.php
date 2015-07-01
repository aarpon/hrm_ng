<?php

// Set up Composer's autoloading
require_once dirname(__FILE__) . '/../vendor/autoload.php';

// Set up Propel
require_once dirname(__FILE__) . '/config.php';

// Set up logging
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Temporary log folder for development. Later, replace
// it with a configurable location.
$log_folder = dirname(__FILE__) . '/../log';

$HRM_LOGGER = new Logger('hrm');
$HRM_LOGGER->pushHandler(new StreamHandler(
        $log_folder . '/development.log',
        Logger::DEBUG));
$HRM_LOGGER->pushHandler(new StreamHandler(
        $log_folder . 'log/production.log',
        Logger::WARNING));

// Settings
// TODO