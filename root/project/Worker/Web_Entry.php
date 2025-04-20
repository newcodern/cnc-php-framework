<?php

require_once dirname(__DIR__) . '/Worker/Autoloader.php';

use Worker\Autoloader;
use Contingent\KomponenFungsiAset;
// Initialize autoloader
Autoloader::register();
Autoloader::addNamespace('Utopia', dirname(__DIR__) . '/Utopia');
Autoloader::addNamespace('Contingent', dirname(__DIR__) . '/Contingent');
Autoloader::addNamespace('Controllers', dirname(__DIR__) . '/Controllers');
Autoloader::addNamespace('Worker', dirname(__DIR__) . '/Worker');

// Load the routes
require_once dirname(__DIR__) . '/Worker/Web.php';