<?php
namespace AdminModule;

use Gittern\Repository,
	Gittern\Transport\NativeTransport,
	Gittern\Configurator,
	Gittern\Gaufrette\GitternCommitishReadOnlyAdapter;
use Gaufrette\Filesystem;

/**
 * MaintenancePresenter
 *
 * @author martin.bazik
 */
class MaintenancePresenter extends SecuredPresenter
{

	protected
			$subTabs = array(
				'DB' => 'db',
				'APC' => 'apc',
				'Elasticsearch' => 'elasticsearch',
				'CRON' => 'cron',
				'redis' => 'redis',
				'log' => 'log'
					),
			/** @var \Admin */
			$me
	;
	
	/** @persistent */
	public $activeTab;

	protected function startup()
	{
		parent::startup();
		$this->me = $this->context->documentManager->find('Admin', $this->user->getId());
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->subTabs = $this->subTabs;
		$this->template->activeTab = $this->activeTab;
	}
	
	public function renderDb($db, $collection, $id)
	{
		$url = $this->getHttpRequest()->getUrl();
		$baseUri = $url->getHostUrl() . $url->getPath();
		$scriptFolder = $this->context->parameters['admin']['scriptFolder'];
		ob_start();
		include $scriptFolder . "/mongodbadmin.php";
		$content = ob_get_clean();
		$this->template->content = $content;
		$this->template->activeTab = 'db';
	}

	public function renderApc()
	{
		$url = $this->getHttpRequest()->getUrl();
		$baseUri = $url->getHostUrl() . $url->getPath();
		$scriptFolder = $this->context->parameters['admin']['scriptFolder'];
		ob_start();
		include $scriptFolder . "/apc.php";
		$content = ob_get_clean();
		$this->template->content = $content;
		$this->template->activeTab = 'apc';
	}

	public function renderRedis()
	{
		$url = $this->getHttpRequest()->getUrl();
		$baseUri = $url->getHostUrl() . $url->getPath();
		$scriptFolder = $this->context->parameters['admin']['scriptFolder'];
		ob_start();
		include $scriptFolder . "/rb.php";
		$content = ob_get_clean();
		$this->template->content = $content;
		$this->template->activeTab = 'redis';
	}

	private function getGitCommit()
	{
		$repoPath = APP_DIR . '/../';
		$repo = new \PHPGit_Repository($repoPath);
		try
		{
			//$repo;
			$commits = $repo->getCommits(1);
			return $commits[0]['id'];
		}
		catch (\GitRuntimeException $e)
		{
			return 'N/A';
		}
		catch (\InvalidGitRepositoryDirectoryException $e)
		{
			return 'N/A';
		}
	}

	public function renderDefault()
	{
		$this->template->netteVersion = \Nette\Framework::VERSION;
		$this->template->vars = array(
			'php_version' => PHP_VERSION,
			'xdebug_enabled' => extension_loaded('xdebug'),
			'eaccel_enabled' => extension_loaded('eaccelerator') && ini_get('eaccelerator.enable'),
			'apc_enabled' => extension_loaded('apc') && ini_get('apc.enabled'),
			'xcache_enabled' => extension_loaded('xcache') && ini_get('xcache.cacher'),
		);
		$this->template->revision = $this->getGitCommit();
	}

	private function generateCronCommand($command)
	{
		return 'php ' . $_SERVER['SCRIPT_FILENAME'] . ' ' . $command;
	}

	public function renderCron()
	{
		$entries = array();
		try
		{
			$cronManager = new \php\manager\crontab\CrontabManager;
			$jobsString = $cronManager->listJobs();
			$parts = explode("\n", $jobsString);



			foreach ($parts as $part)
			{
				if ($part !== "")
				{
					$entries[] = new \php\manager\crontab\CronEntry($part);
				}
			}
		}
		catch (\UnexpectedValueException $e)
		{
			
		}
		$this->template->entries = $entries;
	}

	protected function createComponentFormCron($name)
	{
		$form = new \Forms\BaseForm;
		$form->addText('minute', 'Minute')->setDefaultValue('*');
		$form->addText('hour', 'Hour')->setDefaultValue('*');
		$form->addText('dayMonth', 'Day of month')->setDefaultValue('*');
		$form->addText('month', 'Month')->setDefaultValue('*');
		$form->addText('dayWeek', 'Day of week')->setDefaultValue('*');

		$allCommands = $this->context->console->all();
		$commandNames = array();
		foreach ($allCommands as $commandName => $command)
		{
			if ($command instanceof \Cron)
			{
				$commandNames[$commandName] = $command->getDescription();
			}
		}
		$form->addSelect('cronAction', 'Action', $commandNames);
		$form->addSubmit('btnSubmit', 'Register command');
		$form->onSuccess[] = callback($this, 'FormCronSubmitted');
		return $form;
	}

	public function handleDeleteCronEntry($entry)
	{
		$job = new \php\manager\crontab\CronEntry($entry);
		$cronManager = new \php\manager\crontab\CrontabManager;
		$cronManager->delete($job)->save();
		$this->flash('entry deleted!');
		$this->redirect('this');
	}

	public function formCronSubmitted(\Nette\Application\UI\Form $form)
	{
		$values = $form->values;
		foreach ($values as $variable => $value)
		{
			$$variable = $value;
		}
		//$this->generatedCommand = $this->generateCommand($minute, $hour, $dayMonth, $month, $dayWeek, $cronAction);

		$cronCommand = $this->generateCronCommand($cronAction);

		$cronManager = new \php\manager\crontab\CrontabManager;
		$job = $cronManager->newJob();
		$job->on(sprintf('%s %s %s %s %s', $minute, $hour, $dayMonth, $month, $dayWeek))
				->doJob($cronCommand);
		$cronManager->add($job)->save();

		$this->redirect('this');
	}

	protected function createComponentGridLog()
	{
		$dg = new \Gridder\Gridder;

		$repository = $this->context->documentManager->getRepository('SystemLog');

		$source = new \Gridder\Sources\MongoRepositorySource($repository);
		$storageSection = $this->getSession()->getSection('gridder_logs' . $this->user->id);
		$persister = new \Gridder\Persisters\SessionPersister($storageSection);

		$dg->setSource($source);
		$dg->setPersister($persister);
		$dg->setPresenter($this);

		$dg->addColumn('time', 'datetime')->setFormat('d.m.Y H:i:s')->setSortable();
		$dg->addColumn('who')->setFilter('text');
		$dg->addColumn('message')->setFilter('text');

		return $dg;
	}
	
	private function log()
	{
		
	}

}