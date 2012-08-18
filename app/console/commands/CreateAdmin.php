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
class CreateAdmin extends Console\Command\Command
{

	protected function configure()
	{
		$this
				->setName('app:admin:create')
				->setDescription('Creates admin')
		;
	}

	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$output->writeln('creating new user...');
		$dm = $this->getHelper('dm')->getDocumentManager();

		$password = 'supertajneheslo';
		
		$context = $this->getHelper('containerHelper')->getContainer();
		$hash = $context->passwordHasher->hashPassword($password);
		
		$admin = new \Admin;
		$admin->setEmail('martin@bazo.sk')->setPassword($hash);
		
		$dm->persist($admin);
		try
		{
			$dm->flush($admin, array('safe' => true));
			$output->writeln('<info>admin succesfully created</info>');
		}
		catch(\MongoCursorException $e)
		{
			$output->writeln('<error>admin already exists</error>');
		}
		
	}

}