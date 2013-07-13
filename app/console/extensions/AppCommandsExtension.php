<?php
namespace Extensions;

use Nette\Config\Configurator;

/**
 * Description of AppCommandsExtension
 *
 * @author Martin
 */
class AppCommandsExtension extends \Nette\DI\CompilerExtension
{
	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		// console commands - ODM
		$container->addDefinition($this->prefix('consoleCommandAppCreateUser'))
			->setClass('Console\Command\CreateUser')
			->addTag('consoleCommand');
		
		$container->addDefinition($this->prefix('consoleCommandAppCreateAdmin'))
			->setClass('Console\Command\CreateAdmin')
			->addTag('consoleCommand');
		
		$container->addDefinition($this->prefix('consoleCommandAppDeleteCache'))
			->setClass('Console\Command\DeleteCache')
			->addTag('consoleCommand');
		
		$container->addDefinition($this->prefix('consoleCommandReinstall'))
			->setClass('Console\Command\Reinstall')
			->addTag('consoleCommand');
	}
}