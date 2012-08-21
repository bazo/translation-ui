<?php
namespace FrontModule;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;
/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class TranslationPresenter extends SecuredPresenter
{
	public
		/** @persistent */	
		$id,
		
		/** @persistent */	
		$filter = 'all',
		
		/** @persistent */	
		$page = 1
	;
	
	private 
		/** @var \Translation */	
		$translation,
			
		$maxItems = 5
	;

	protected function startup()
	{
		parent::startup();
		$this->id = $this->getParameter('id');
		$this->translation = $this->context->translationFacade->find($this->id);
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
	
	public function renderDefault($filter)
	{
		parent::beforeRender();
		$this->template->filter = $filter;
		$this->template->translation = $this->translation;
		
		$messages = $this->context->translationFacade->findFilteredMessages($this->id, $filter, $this->page, $this->maxItems);
		
		$totalCount = $messages->count();
		
		$pagesCount = (int)round($totalCount / $this->maxItems);
		
		$this->template->pagesCount = $pagesCount;
		$this->template->page = $this->page;
		$this->template->messages = $messages;
	}
	
	private function formatDownloadName(\Translation $translation, $ext)
	{
		return $translation->getProject()->getCaption().'-'.$translation->getLang().'.'.$ext;
	}
	
	public function handleDownload()
	{
		$dictionary = $this->context->translationFacade->getDictionary($this->translation);
		$data = serialize($dictionary);
		$name = $this->translation->getProject()->getCaption().'-'.$this->translation->getLang().'.dict';
		
		$fileName = $this->context->parameters['tempDir'].'/'.$this->translation->getId().'-'.$name;
		
		file_put_contents($fileName, $data);
		
		$response = new \Nette\Application\Responses\FileResponse($fileName, $name, 'text/plain');
		
		//$response = new \Responses\TextDownloadResponse(chr(239) . chr(187) . chr(191).$data, $name, 'text/x-neon', 'UTF-8');
		
		$this->sendResponse($response);
		$this->terminate();
	}
	
	public function handleDownloadTranslation()
	{
		$dictionaryData = $this->context->translationFacade->getDictionaryData($this->translation);

		$builder = new \Mazagran\Translation\Builder;
		$data = $builder->dump($dictionaryData);
		
		$name = $this->formatDownloadName($this->translation, 'neon');
		$response = new \Responses\TextDownloadResponse($data, $name, 'text/x-neon', 'UTF-8');
		
		$this->sendResponse($response);
		$this->terminate();
	}
	
	protected function createComponentFormNewMessage()
	{
		$form = new Form;
		$form->addText('context', 'Context');
		$form->addText('singular', 'Singular')->setRequired();
		$form->addText('plural', 'Plural');
		$form->addSubmit('btnSubmit', 'Add');
		
		$form->onSuccess[] = callback($this, 'formNewMessageSubmitted');
		
		return $form;
	}
	
	public function formNewMessageSubmitted(Form $form)
	{
		$values = $form->getValues();
		
		$project = $this->translation->getProject();
		try
		{
			$this->context->translationFacade->addMessageToProject($project, $values);
		}
		catch(\ExistingMessageException $e)
		{
			$this->flash($e->getMessage(), 'error');
		}
		
		
		$this->redirect('this');
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
		
		if($values->translation->isOk())
		{
			$neon = file_get_contents($values->translation->getTemporaryFile());
			$data = \Nette\Utils\Neon::decode($neon);
			
			$this->context->translationFacade->importTranslation($data, $this->translation);
		}
	}
	
	protected function createComponentFormTranslate()
	{
		$presenter = $this;
		return new \Nette\Application\UI\Multiplier(function($id, $control) use ($presenter){
			
			$message = $presenter->context->messageFacade->find($id);
			
			$form = new Form;
			$form->addHidden('id', $id);
			$form->addHidden('plural', $message->hasPlural());
			$translations = $form->addContainer('translations');
			
			if($message->hasPlural())
			{
				for($i = 0; $i < $message->getPluralsCount(); $i++)
				{
					$translations->addTextArea(sprintf('%d', $i), sprintf('Plural form %d', $i));
				}
			}
			else
			{
				$translations->addTextArea(0, 'Translation');
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
		
		$message = $this->context->messageFacade->find($values->id);
		
		$translations = array();
		if($message->hasPlural())
		{
			for($i = 0; $i < $message->getPluralsCount(); $i++)
			{
				$translations[$i] = $values->translations->{$i};
			}
		}
		else
		{
			$translations[0] = $values->translations->{0};
		}
		
		$this->context->messageFacade->translateMessage($message, $translations);
		
		if(!$this->isAjax())
		{
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
		$message = $this->context->messageFacade->find($values->id);
		
		if($this->translation->hasMessage($message))
		{
			$this->context->messageFacade->delete($message);
			$this->flash(sprintf('Message "%s" has been deleted.', $message->getSingular()));
		}
		
		$this->redirect('this');
	}

}
