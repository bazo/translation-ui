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
class CreateAdmin extends Console\Command\Command
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
				->setName('app:admin:create')
				->setDescription('Creates admin')
				->addArgument('email', InputArgument::OPTIONAL, 'email?')
				->addArgument('password', InputArgument::OPTIONAL, 'password?')
		;
	}


	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$output->writeln('creating new admin...');

		$dm = $this->dm;

		$dialog = new Console\Helper\DialogHelper;

		$email = $input->getArgument('email');
		$password = $input->getArgument('password');

		if ($email === null) {
			$email = $dialog->ask($output, '<question>please provide email for the new user: </question>', null);

			if ($email === null) {
				$output->writeln('<error>you have to provide email. aborting.</error>');
				return;
			}

			$password = $dialog->ask($output, '<question>please provide password for the user ' . $email . ': </question>', null);

			if ($password === null) {
				$output->writeln('<error>you have to provide password. aborting.</error>');
				return;
			}
		}

		if ($email !== null and $password === null) {
			$password = $dialog->ask($output, '<question>please provide password for the user ' . $email . ': </question>', null);
			if ($password === null) {
				$output->writeln('<error>you have to provide password. aborting.</error>');
				return;
			}
		}

		if ($dm->getRepository('User')->findOneByLogin($email) !== null) {
			$output->writeln('<error>user with email ' . $email . ' already exists. aborting.</error>');
			return;
		}
		$admin = new \Admin;
		$hash = password_hash($password, PASSWORD_BCRYPT);
		$admin->setEmail('martin@bazo.sk')->setPassword($hash);

		$dm->persist($admin);
		//$dm->flush(array('safe' => true)); //throws some bullshit error, thus checking by finding by email
		try {
			$this->dm->flush($admin, array('safe' => true));
			$output->writeln('<info>admin succesfully created</info>');
		} catch (\MongoCursorException $e) {
			$output->writeln('<error>admin already exists</error>');
		}
	}


}

