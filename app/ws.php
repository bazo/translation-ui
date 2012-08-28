<?php
use WS\Controller;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Session\SessionProvider;
use Ratchet\Wamp\WampServer;
use Nette\Application\Routers\Route;

// absolute filesystem path to the application root
define('APP_DIR', __DIR__);

// absolute filesystem path to the libraries
define('LIBS_DIR', APP_DIR . '/../libs');

// Load Nette Framework
define('VENDOR_DIR', APP_DIR.'/../vendor');
require VENDOR_DIR.'/autoload.php';

// Configure application
$configurator = new Nette\Config\Configurator;
$debug = Nette\Diagnostics\Debugger::DEVELOPMENT;
if(isset($_SERVER['MODE']))
{
    $mode = $_SERVER['MODE'];
	if($mode === 'production')
	{
		$debug = Nette\Diagnostics\Debugger::PRODUCTION;
	}
}

$configurator->setDebugMode(!$debug);
// Enable Nette Debugger for error visualisation & logging
Nette\Diagnostics\Debugger::$strictMode = TRUE;
Nette\Diagnostics\Debugger::enable($debug, __DIR__ . '/../log', 'martin@bazo.sk');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(LIBS_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon', 'ws');
$localConfigFile = __DIR__ . '/config/config.local.neon';
if(file_exists($localConfigFile))
{
	$configurator->addConfig($localConfigFile, $configurator::NONE);
}

$configurator->onCompile[] = function($configurator, $compiler) {
		$compiler->addExtension('documentManagerExtension', new \Bazo\Extensions\DocumentManager);
};
Kdyby\Extension\Redis\DI\RedisExtension::register($configurator);

$container = $configurator->createContainer();

// Setup router
$container->router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
$container->router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');

$port = 8000;

//$wsServer = new WsServer(new WampServer(new MyApp));

#ini_set('session.name', 'websockets');
/*
$session = new SessionProvider(
        new Controller($container)
      , $container->symfonyStorageHandler,
		array('name' => 'websockets', 'auto_start' => true)
    );
*/
$session = new Bazo\Ratchet\NetteSessionProvider(new Controller($container), $container->session);

$wsServer = new WsServer($session);

// Make sure to run as root
$server = IoServer::factory($wsServer, $port);
$server->run();