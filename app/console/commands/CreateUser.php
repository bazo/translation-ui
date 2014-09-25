<?php

namespace Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console;
use Doctrine\ODM\MongoDB\DocumentManager;



/**
 * Description of CreateUser
 *
 * @author Martin
 */
class CreateUser extends Console\Command\Command
{

	/** @var \Facades\User */
	private $userFacade;

	function __construct(\Facades\User $userFacade)
	{
		parent::__construct();
		$this->userFacade = $userFacade;
	}


	protected function configure()
	{
		$this
				->setName('app:user:create')
				->setDescription('Creates a user')
				->addArgument('nick', InputArgument::OPTIONAL, 'nick?')
				->addArgument('email', InputArgument::OPTIONAL, 'email?')
				->addArgument('password', InputArgument::OPTIONAL, 'password?')
		;
	}


	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$output->writeln('creating new user...');

		$dialog = new Console\Helper\DialogHelper;

		$nick = $input->getArgument('nick');
		$email = $input->getArgument('email');
		$password = $input->getArgument('password');

		if ($nick === NULL) {
			$nick = $dialog->ask($output, '<question>please provide nick for the new user: </question>', NULL);

			if ($nick === NULL) {
				$output->writeln('<error>you have to provide nick. aborting.</error>');
				return;
			}

			$password = $dialog->ask($output, '<question>please provide password for the user ' . $nick . ': </question>', NULL);

			if ($password === NULL) {
				$output->writeln('<error>you have to provide password. aborting.</error>');
				return;
			}
		}

		if ($email === NULL) {
			$email = $dialog->ask($output, '<question>please provide email for the new user: </question>', NULL);

			if ($email === NULL) {
				$output->writeln('<error>you have to provide email. aborting.</error>');
				return;
			}
		}

		if ($nick !== NULL and $password === NULL) {
			$password = $dialog->ask($output, '<question>please provide password for the user ' . $nick . ': </question>', NULL);
			if ($password === NULL) {
				$output->writeln('<error>you have to provide password. aborting.</error>');
				return;
			}
		}

		$this->userFacade->createUser($nick, $email, $password);

		$output->writeln('<info>user ' . $nick . ' succesfully created</info>');
	}


}