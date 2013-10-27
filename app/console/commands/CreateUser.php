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

	/** @var DocumentManager */
	private $dm;


	function __construct(DocumentManager $dm)
	{
		parent::__construct();
		$this->dm = $dm;
	}


	protected function configure()
	{
		$this
				->setName('app:user:create')
				->setDescription('Creates a user')
				->addArgument('login', InputArgument::OPTIONAL, 'login?')
				->addArgument('password', InputArgument::OPTIONAL, 'password?')
		;
	}


	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$output->writeln('creating new user...');
		$dm = $this->dm;

		$dialog = new Console\Helper\DialogHelper;

		$login = $input->getArgument('login');
		$password = $input->getArgument('password');

		if ($login === null) {
			$login = $dialog->ask($output, '<question>please provide login for the new user: </question>', null);

			if ($login === null) {
				$output->writeln('<error>you have to provide login. aborting.</error>');
				return;
			}

			$password = $dialog->ask($output, '<question>please provide password for the user ' . $login . ': </question>', null);

			if ($password === null) {
				$output->writeln('<error>you have to provide password. aborting.</error>');
				return;
			}
		}

		if ($login !== null and $password === null) {
			$password = $dialog->ask($output, '<question>please provide password for the user ' . $login . ': </question>', null);
			if ($password === null) {
				$output->writeln('<error>you have to provide password. aborting.</error>');
				return;
			}
		}

		if ($dm->getRepository('User')->findOneByLogin($login) !== null) {
			$output->writeln('<error>user with login ' . $login . ' already exists. aborting.</error>');
			return;
		}
		$user = new \User;
		$hash = password_hash($password, PASSWORD_BCRYPT);
		$user->setLogin($login)->setPassword($hash);

		$dm->persist($user);
		//$dm->flush(array('safe' => true)); //throws some bullshit error, thus checking by finding by login
		$dm->flush();
		$output->writeln('<info>user ' . $login . ' succesfully created</info>');
	}


}

