<?php
/**
 * ApiPresenter
 *
 * @author martin.bazik
 */
class ApiPresenter extends \Nette\Application\UI\Presenter
{
	
	private $map = array(
		'log' => 'log'
	);
	
	protected function startup()
	{
		parent::startup();
		Nette\Diagnostics\Debugger::$productionMode = true;
		$request = $this->getHttpRequest();
		if($request->getMethod() !== 'POST')
		{
			$payload = array(
				'error' => 'Only POST requests allowed'
			);
			$this->sendResponse(new Nette\Application\Responses\JsonResponse($payload));
		}
		else
		{
			$post = $this->getHttpRequest()->getPost();
			if(isset($post['data']))
			{
				$data = json_decode($post['data']);
				if(isset($data->appId) and isset($data->appKey))
				{
					try
					{
						$app = $this->context->appService->authenticateApp($data->appId, $data->appKey);
						$params = $this->getRequest()->getParameters();
						$action = $params['action'];
						$method = $this->map[$action];

						$level = $data->level;
						$message = $data->message;
						if(isset($data->nette) and $data->nette === true)
						{
							if($method === 'log')
							{
								$this->logNette($app, $level, $data->netteMessage);
							}
						}
						else
						{
							$this->$method($app, $level, $message);
						}
						
					}
					catch(\Nette\Security\AuthenticationException $e)
					{
						$payload = array(
							'error' => $e->getMessage()
						);
						$this->sendResponse(new Nette\Application\Responses\JsonResponse($payload));
					}
				}
			}
		}
		$this->terminate();
	}
	
	private function log(App $app, $level, $message)
	{
		$this->context->logService->setPresenter($this);
		if(($log = $this->context->logService->logWithMessageExists($app, $message)) !== false)
		{
			$this->context->logService->updateByMessage($app, $message);
		}
		else
		{
			$this->context->logService->create($app, $level, $message);
		}
	}
	
	private function logNette(App $app, $level, $netteMessage)
	{
		$this->context->logService->setPresenter($this);
		
		unset($netteMessage[0]);
		$message = implode(' ', $netteMessage);
		
		if(($log = $this->context->logService->logWithMessageExists($app, $message)) !== false)
		{
			$this->context->logService->updateByMessage($app, $message);
		}
		else
		{
			$this->context->logService->create($app, $level, $message);
		}
	}
}