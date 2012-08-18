<?php
namespace Services;
use Doctrine\ODM\MongoDB\DocumentManager;
class LogService
{
	private
		/** @var DocumentManager */	
		$documentManager,
		/** @var \Pusher */	
		$pusher,
			
		$presenter
	;
	
	public function __construct(DocumentManager $documentManager, \Pusher $pusher)
	{
		$this->documentManager = $documentManager;
		$this->pusher = $pusher;
	}
	
	public function setPresenter($presenter)
	{
		$this->presenter = $presenter;
	}

	/**
	 * @param type $id
	 * @return \App
	 */
	public function find($id)
	{
		return $this->documentManager->getRepository('Log')->find($id);
	}
	
	public function create(\App $app, $level, $message)
	{
		$log = new \Log;
		$log->setApp($app);
		
		$log->setLevel($level)->setMessage($message)->setUser($app->getUser())
				->addIndex($app->getId())->addIndex($app->getName())
				->addIndex($app->getUser()->getId())->addIndex($log->getLevel());

		$app->addLog($log);
		$this->documentManager->persist($log);
		$this->documentManager->persist($this->updateAppAlertCount($app, $level));
		
		$user = $app->getUser();
		
		if($this->sendLiveUpdate($user, $app, $log))
		{
			$alert = $this->renderAlert($log);

			$values = array(
				'alert' => $alert,
				'appName' => $app->getName(),
				'level' => $log->getLevel()
			);

			$this->pusher->trigger('private-'.$app->getUser()->getId(), 'alert', json_encode($values));
		}
		try
		{
			$this->documentManager->flush();
			return $log;
		}
		catch(\MongoCursorException $e)
		{
			//fail silently
		}
	}
	
	public function logWithMessageExists(\App $app, $message)
	{
		$qb = $this->documentManager->getRepository('Log')->createQueryBuilder();
		$lastAppLog = $qb->field('appName')->equals($app->getName())->field('user')->references($app->getUser())
				->sort('added', 'desc')
				->getQuery()->getSingleResult();
		if($lastAppLog === null or $lastAppLog->getMessage() !== $message)
		{
			return false;
		}
		return $lastAppLog;
	}
	
	public function updateByMessage(\App $app, $message)
	{
		$qb = $this->documentManager->getRepository('Log')->createQueryBuilder();
		$lastAppLog = $qb->field('appName')->equals($app->getName())->field('user')->references($app->getUser())
				->field('message')->equals($message)->sort('added', 'desc')
				->getQuery()->getSingleResult();

		$values = array(
			'logId' => $lastAppLog->getId()
		);

		$this->pusher->trigger('private-'.$app->getUser()->getId(), 'updateCount', json_encode($values));
		
		$lastAppLog->addCount();
		
		$this->documentManager->persist($this->updateAppAlertCount($app, $lastAppLog->getLevel()));
		
		$this->documentManager->persist($lastAppLog);
		$this->documentManager->flush();
	}
	
	private function updateAppAlertCount(\App $app, $level)
	{
		return $app->addError(new \DateTime, $level);
	}
	
	private function renderAlert(\Log $log)
	{
		$template = new \Nette\Templating\FileTemplate;
		$template->registerFilter(new \Nette\Latte\Engine);
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');
		$template->setFile(APP_DIR.'/templates/alert.latte');
		$template->control = $template->_control = $template->presenter = $template->_presenter = $this->presenter;
		$template->log = $log;
		return $template->__toString();
	}
	
	private function sendLiveUpdate(\User $user, \App $app, \Log $log)
	{
		$send = false;
		
		$settings = $user->getSettings();
		
		if(!empty($settings['filters']))
		{
			$appNames = array_keys(array_filter($settings['filters']));
			$levels = array_keys(array_filter($settings['levels']));
			
			if(in_array($app->getName(), $appNames) and in_array($log->getLevel(), $levels))
			{
				$send = true;
			}
		}
		
		return $send;
	}
	
}
