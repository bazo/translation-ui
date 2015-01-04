<?php

namespace FrontModule;

use Activity;
use ExistingMessageException;
use Facades\Message;
use Facades\Translation as Translation2;
use KdybyTranslationBuilder;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Neon\Neon;
use Responses\TextDownloadResponse;
use Translation;



class TranslationPresenter extends SecuredPresenter
{

	/** @var Translation */
	private $translation;
	private $maxItems = 10;

	/** @var Translation2 @inject */
	public $translationFacade;

	/** @var Message @inject */
	public $messageFacade;

	/** @persistent */
	public $id;

	/** @persistent */
	public $filter = 'all';

	/** @persistent */
	public $search = NULL;

	/** @persistent */
	public $page = 1;

	protected function startup()
	{
		parent::startup();
		$this->id = $this->getParameter('id');
		$this->translation = $this->translationFacade->find($this->id);
	}


	public function handleFilter($filter)
	{
		$this->filter = $filter;
		$this->redirect('this');
	}


	public function handleChangePage($page)
	{
		$this->page = $page;
		$this->redirect('this');
	}


	public function renderDefault($filter, $search)
	{
		$this->template->filter = $filter;
		$this->template->translation = $this->translation;

		$messages = $this->translationFacade->findFilteredMessages($this->id, $filter, $search, $this->page, $this->maxItems);

		$totalCount = $messages->count();

		$pagesCount = (int) round($totalCount / $this->maxItems);

		$this->template->pagesCount = $pagesCount;
		$this->template->page = $this->page;
		$this->template->messages = $messages;
	}


	public function handleDownloadTranslation()
	{
		$dictionaryData = $this->translationFacade->getDictionaryData($this->translation);
		$builder = new KdybyTranslationBuilder;

		$mask = '%s.' . $this->translation->getLocale() . '.neon';

		$outputFiles = $builder->build($mask, $dictionaryData);

		if (count($outputFiles) === 1) {
			$name = key($outputFiles);
			$neon = Neon::encode(current($outputFiles), Neon::BLOCK);
			$response = new TextDownloadResponse($neon, $name, 'text/x-neon', 'UTF-8');
			$this->sendResponse($response);
			$this->terminate();
		} else {
			$zip = new \ZipStream(sprintf('%s - %s.zip', $this->translation->getProject()->getName(), $this->translation->getLocale()));
			foreach ($outputFiles as $fileName => $data) {
				$neon = Neon::encode($data, Neon::BLOCK);
				$zip->add_file($fileName, $neon);
			}
			$zip->finish();
		}
	}


	protected function createComponentFormNewMessage()
	{
		$form = new Form;
		$form->addText('context', 'Context')->setDefaultValue('messages');
		$form->addText('singular', 'Singular')->setRequired();
		//$form->addText('plural', 'Plural');
		$form->addHidden('plural', '');
		$form->addSubmit('btnSubmit', 'Add');
		$form->onSuccess[] = callback($this, 'formNewMessageSubmitted');

		return $form;
	}


	public function formNewMessageSubmitted(Form $form)
	{
		$values = $form->getValues();
		$project = $this->translation->getProject();
		try {
			$message = $this->translationFacade->addMessageToProject($project, $values);
			$this->log($project, Activity::ADD_MESSAGE, $message);
		} catch (ExistingMessageException $e) {
			$this->flash($e->getMessage(), 'error');
		}

		$this->invalidateControl('aside');
		$this->invalidateControl('messages');
	}


	protected function createComponentFormImportTranslation()
	{
		$form = new Form;

		$form->addUpload('translation', 'Translation');
		$form->addSubmit('btnSubmit', 'Import');

		$form->onSuccess[] = callback($this, 'formImportTranslationSubmitted');

		return $form;
	}


	public function formImportTranslationSubmitted(Form $form)
	{
		$values = $form->getValues();

		if ($values->translation->isOk()) {
			$neon = file_get_contents($values->translation->getTemporaryFile());
			$data = \Nette\Utils\Neon::decode($neon);

			$this->translationFacade->importTranslation($data, $this->translation);
		}

		$this->redirect('this');
	}


	protected function createComponentFormImportPOTranslation()
	{
		$form = new Form;

		$form->addUpload('translation', 'Translation');
		$form->addSubmit('btnSubmit', 'Import');

		$form->onSuccess[] = callback($this, 'formImportPOTranslationSubmitted');

		return $form;
	}


	public function formImportPOTranslationSubmitted(Form $form)
	{
		$values = $form->getValues();

		if ($values->translation->isOk()) {
			$this->translationFacade->importPOTranslation($values->translation, $this->translation);
		}
	}


	protected function createComponentFormTranslate()
	{
		$presenter = $this;
		return new Multiplier(function($id, $control) use ($presenter) {

			$message = $presenter->messageFacade->find($id);

			$form = new Form;
			$form->addHidden('id', $id);
			$form->addHidden('plural', $message->hasPlural());
			$translations = $form->addContainer('translations');

			for ($i = 0; $i < $message->getPluralsCount(); $i++) {
				if ($i === 0) {
					$translations->addTextArea(sprintf('%d', $i), 'Singular');
				} else {
					$translations->addTextArea(sprintf('%d', $i), sprintf('Plural form %d', $i));
				}
			}

			$form->addSubmit('btnSubmit', 'Save');

			$form->setDefaults(array(
				'translations' => $message->getTranslations()
			));

			$form->onSuccess[] = callback($presenter, 'formTranslateSubmitted');

			return $form;
		});
	}


	public function formTranslateSubmitted(Form $form)
	{
		$values = $form->getValues();

		$message = $this->messageFacade->find($values->id);

		if (count($values->translations) > 1) {
			$activity = Activity::TRANSLATE_PLURAL;
		} else {
			$activity = Activity::TRANSLATE_SINGULAR;
		}

		$translations = array();
		$activity = Activity::TRANSLATE_PLURAL;
		for ($i = 0; $i < $message->getPluralsCount(); $i++) {
			$translations[$i] = $values->translations->{$i};
		}

		$this->messageFacade->translateMessage($message, $translations);

		$this->log($this->translation->getProject(), $activity, $message);

		if ($this->isAjax()) {
			$this->terminate();
		} else {
			$this->redirect('this');
		}
	}


	protected function createComponentFormDeleteMessage()
	{
		$form = new Form;

		$form->addSubmit('btnSubmit', 'Delete');
		$form->addHidden('id');

		$form->onSuccess[] = callback($this, 'formDeleteMessageSubmitted');

		return $form;
	}


	public function formDeleteMessageSubmitted(Form $form)
	{
		$values = $form->getValues();

		$message = $this->messageFacade->find($values->id);

		if ($this->translation->hasMessage($message)) {
			$this->messageFacade->delete($message);
			$this->log($this->translation->getProject(), Activity::DELETE_MESSAGE, $message);
			$this->flash(sprintf('Message "%s" has been deleted.', $message->getSingular()));
		}

		$this->redirect('this');
	}


	protected function createComponentFormSearch()
	{
		$form = new Form;

		$form->addText('search', 'Search');
		$form->addSubmit('btnSubmit', 'Search');

		$form->onSuccess[] = callback($this, 'formSearchSubmitted');

		return $form;
	}


	public function formSearchSubmitted(Form $form)
	{
		$values = $form->getValues();

		$this->search = $values->search;

		$this->redrawControl('aside');
		$this->redrawControl('messages');
	}


}