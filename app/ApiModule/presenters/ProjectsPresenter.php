<?php

namespace ApiModule;

/**
 */
class ProjectsPresenter extends BasePresenter
{

	/** @var \Facades\Project  @inject */
	public $projectFacade;

	protected function startup()
	{
		parent::startup();
	}


	public function actionUpdate($id, $hash)
	{
		$project = $this->projectFacade->find($id);

		$data = $this->getHttpRequest()->getRawBody();

		$verifyHash = hash_hmac('sha256', $data, $project->getKey());

		if ($hash === $verifyHash) {
			$messageData = unserialize($data);
			$imported = $this->projectFacade->importTemplate($messageData, $project);
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse(['error' => 'false', 'message' => sprintf('OK. Imported %d messages.', $imported)]));
		} else {
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse(['error' => 'true', 'message' => 'Bad request. Hash does not match.']));
		}

		$this->terminate();
	}


}

