<?php
namespace FrontModule;
/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends \Base\BasePresenter
{
	protected
		/** @var User */	
		$me
	;
	
	protected function startup()
	{
		parent::startup();
		$this->user->getStorage()->setNamespace('user');
		if($this->user->isLoggedIn())
		{
			$this->me = $this->context->userFacade->find($this->user->getId());
		}
	}
	
	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->me = $this->me;
	}
	
	public function handleAuthenticate()
	{
		Nette\Diagnostics\Debugger::enable(true);
		$post = $this->getHttpRequest()->getPost();
		$socket_id = $post['socket_id'];
		$channel_name = $post['channel_name'];
		
		$userId = Nette\Utils\Strings::substring($channel_name, 8);
		
		if($this->user->getId() === $userId)
		{
			$json = $this->context->pusher->socket_auth($channel_name, $socket_id);
			$payload = json_decode($json);
			$response = new Nette\Application\Responses\JsonResponse($payload);
			$this->sendResponse($response);
		}
		else
		{
			$this->getHttpResponse()->setCode(403);
			$this->getHttpResponse()->setHeader('', 'Forbidden');
		}
		$this->terminate();
	}
}
