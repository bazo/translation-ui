<?php

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

// Load Nette Framework
define('VENDORS_DIR', __DIR__ . '/../vendor');
require_once VENDORS_DIR . '/autoload.php';

// Configure application
$configurator = new Nette\Config\Configurator;

if (isset($_SERVER['ENVIRONMENT'])) {
	$environment = $_SERVER['ENVIRONMENT'];
} else {
	$environment = 'local';
}
$debug = Nette\Diagnostics\Debugger::DEVELOPMENT;
if (isset($_SERVER['MODE'])) {
	$mode = $_SERVER['MODE'];
	if ($mode === 'production') {
		$debug = Nette\Diagnostics\Debugger::PRODUCTION;
	}
}

$configurator->setDebugMode($debug);
// Enable Nette Debugger for error visualisation & logging
Nette\Diagnostics\Debugger::$strictMode = FALSE; //don't throw exceptions for deprecated features
Nette\Diagnostics\Debugger::enable($debug, __DIR__ . '/../log', 'martin@bazo.sk');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
		->addDirectory(APP_DIR)
		->addDirectory(LIBS_DIR)
		->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');

$environmentConfig = __DIR__ . sprintf('/config/config.%s.neon', $environment);

$localConfigFile = __DIR__ . '/config/config.local.neon';
if (file_exists($localConfigFile)) {
	$configurator->addConfig($localConfigFile);
}

$container = $configurator->createContainer();

$container->router[] = $apiRouter = new RouteList('Api');
$apiRouter[] = new Routes\RestRoute('api/<presenter>/<id>', array(
		), Routes\RestRoute::RESTFUL);


$container->router[] = $adminRouter = new RouteList('Admin');
$adminRouter[] = new Route('admin/<presenter>/<action>[/<id>]', array(
	'presenter' => 'dashboard',
	'action' => 'default'
		));

if ($container->user->isLoggedIn()) {
	$container->router[] = new Route('/<presenter>/<action>[/<id>]', array(
		'module' => 'front',
		'presenter' => 'stream',
		'action' => 'default'
	));
} else {
	$container->router[] = new Route('/<presenter>/<action>[/<id>]', array(
		'module' => 'front',
		'presenter' => 'homepage',
		'action' => 'default'
	));
}

return $container;
