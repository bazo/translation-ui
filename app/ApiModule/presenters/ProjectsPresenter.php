<?php

namespace ApiModule;


use KdybyTranslationBuilder;
use Nette\Neon\Neon;
use ZipStream\ZipStream;

/**
 */
class ProjectsPresenter extends BasePresenter
{

	/** @var \Facades\Project  @inject */
	public $projectFacade;

	/** @var \Facades\Translation @inject */
	public $translationFacade;

	/** @var \Monolog\Logger @inject */
	public $logger;

	protected function startup()
	{
		parent::startup();
	}


	public function actionUpdate($id, $hash)
	{

		$project = $this->projectFacade->find($id);

		$data = $this->getHttpRequest()->getRawBody();

		$verifyHash = hash_hmac('sha256', $data, $project->getKey());

		$messageData = unserialize($data);

		$this->logger->addInfo('importing ', $messageData);

		if ($hash === $verifyHash) {

			$imported	 = $this->projectFacade->importTemplate($messageData, $project);
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse(['error' => 'false', 'message' => sprintf('OK. Imported %d messages.', $imported)]));
		} else {
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse(['error' => 'true', 'message' => 'Bad request. Hash does not match.']));
		}

		$this->terminate();
	}


	public function actionDownloadTranslations($id, $hash)
	{
		$project = $this->projectFacade->find($id);

		$verifyHash = hash_hmac('sha256', $id, $project->getKey());

		if ($hash === $verifyHash) {
			$this->buildFiles($project);
		} else {
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse(['error' => 'true', 'message' => 'Bad request. Hash does not match.']));
		}

		$this->terminate();
	}


	private function buildFiles(\Project $project)
	{
		$translations = $project->getTranslations();

		$builder = new KdybyTranslationBuilder;

		$files = [];

		foreach ($translations as $translation) {
			$mask			 = '%s.' . $translation->getLocale() . '.neon';
			$dictionaryData	 = $this->translationFacade->getDictionaryData($translation);
			$outputFiles	 = $builder->build($mask, $dictionaryData);
			$files			 = array_merge($files, $outputFiles);
		}

		$zip = new ZipStream(sprintf('%s.zip', $project->getName()));

		foreach ($files as $fileName => $messages) {
			$data = Neon::encode($messages, Neon::BLOCK);
			$zip->addFile($fileName, $data);
		}

		$zip->finish();
	}


}
