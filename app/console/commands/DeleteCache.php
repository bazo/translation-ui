<?php
namespace Console\Command;

use Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console;

/**
 * Description of CreateUser
 *
 * @author Martin
 */
class DeleteCache extends Console\Command\Command
{

	private $tempDir;
	
	function __construct($tempDir)
	{
		parent::__construct();
		$this->tempDir = $tempDir;
	}


	
	protected function configure()
	{
		$this
				->setName('app:cache:delete')
				->setDescription('Deletes cache')
		;
	}

	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		
		$cacheDir = $this->tempDir.'/cache';
		
		$output->writeln(sprintf('deleting cache directory %s ', $cacheDir));
		
		exec(sprintf('rm -r "%s"', $cacheDir));
		
		$output->writeln('<info>done</info>');
	}

}