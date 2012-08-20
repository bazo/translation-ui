<?php
namespace Console\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console;
use Symfony\Component\Console\Input\StringInput;

/**
 * DownloadFeedsCommand
 *
 * @author martin.bazik
 */
class Reinstall extends Console\Command\Command
{
	protected function configure()
    {
        $this
            ->setName('app:reinstall')
            ->setDescription('Reinstalls app.')
        ;
    }
	
	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
		$output->writeln('Reinstalling app');
		$application = $this->getApplication();		
		
		$command = $application->get('app:cache:delete');
		$command->run($input, $output);
		
		$command = $application->get('odm:schema:drop');
		$command->run($input, $output);
		
		$command = $application->get('odm:schema:create');
		$command->run($input, $output);
		
		$command = $application->get('odm:generate:hydrators');
		$command->run($input, $output);
		
		$command = $application->get('odm:generate:proxies');
		$command->run($input, $output);
		
		$command = $application->get('app:admin:create');
		$command->run($input, $output);

		$output->writeln('Finished');
    }
}