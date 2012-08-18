<?php
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

// Load Nette Framework
define('VENDORS_DIR', __DIR__ . '/../vendor');
require_once VENDORS_DIR . '/autoload.php';

// Configure application
$configurator = new Nette\Config\Configurator;

if(isset($_SERVER['ENVIRONMENT']))
{
    $environment = $_SERVER['ENVIRONMENT'];
	
}
else
{
    $environment = 'local';
}
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
$configurator->addConfig(__DIR__ . '/config/config.neon', $environment);
$localConfigFile = __DIR__ . '/config/config.local.neon';
if(file_exists($localConfigFile))
{
	$configurator->addConfig($localConfigFile, $configurator::NONE);
}

$configurator->onCompile[] = function($configurator, $compiler) {
		$compiler->addExtension('documentManagerExtension', new \Bazo\Extensions\DocumentManager);
		$compiler->addExtension('appCommands', new \Extensions\AppCommandsExtension);
        $compiler->addExtension('doctrineODMCommands', new \Bazo\Extensions\DoctrineODMCommands);
		$compiler->addExtension('consoleApp', new \Extensions\ConsoleExtension);
};

$container = $configurator->createContainer();

if(PHP_SAPI === 'cli')
{
	$container->console->run();
}
else
{
	// Setup router
	//$container->router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
	
	$container->router[] = $adminRouter = new RouteList('Admin');
	$adminRouter[] = new Route('admin/<presenter>/<action>[/<id>]', array(
		'presenter' => 'dashboard',
		'action' => 'default'
	));
	
	if($container->user->isLoggedIn())
	{
		$container->router[] = new Route('/<presenter>/<action>[/<id>]', array(
			'module' => 'front',
			'presenter' => 'stream',
			'action' => 'default'
		));
	}
	else
	{
		$container->router[] = new Route('/<presenter>/<action>[/<id>]', array(
			'module' => 'front',
			'presenter' => 'homepage',
			'action' => 'default'
		));
	}

	// Configure and run the application!
	$container->application->run();
}