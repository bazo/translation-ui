<?php
namespace ApiModule;
/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ProjectsPresenter extends BasePresenter
{

	protected function startup()
	{
		parent::startup();
		
		
	}
	
	public function actionUpdate($id, $messageData, $hash)
	{
		$project = $this->context->projectFacade->find($id);
		
		$verifyHash = hash_hmac('sha256', $messageData, $project->getKey());

		if($hash === $verifyHash)
		{
			$messageData = unserialize($messageData);
			$imported = $this->context->projectFacade->importTemplate($messageData, $project);
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse(array('error' => 'false', 'message' => sprintf('OK. Imported %s messages.', $imported))));
		}
		else
		{
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse(array('error' => 'true', 'message' => 'Bad request. Hash does not match.')));
		}
		
		$this->terminate();
	}

}
