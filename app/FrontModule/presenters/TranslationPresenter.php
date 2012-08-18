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
		$filter = 'all'
	;
	
	private 
		/** @var \Translation */	
		$translation
	;

	protected function startup()
	{
		parent::startup();
		$this->id = $this->getParameter('id');
		$this->translation = $this->context->translationFacade->find($this->id);
	}

	public function handleDownload()
	{
		
	}
	
	protected function beforeRender()
	{
		parent::beforeRender();
		
		$this->template->translation = $this->translation;
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
		
		$message = new \Message;
		$message->setContext($values->context)->setSingular($values->singular);
		
		if($values->plural !== '')
		{	
			$message->setPlural($values->plural);
		}
		$project = $this->translation->getProject();
		
		$this->context->translationFacade->addMessageToProject($project, $message);
		
		$this->redirect('this');
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
				$translations->addTextArea('0', 'Translation');
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
		for($i = 0; $i < $message->getPluralsCount(); $i++)
		{
			$translations[$i] = $values->translations->{$i};
		}
		
		$this->context->messageFacade->translateMessage($message, $translations);
		
		if(!$this->isAjax())
		{
			$this->redirect('this');
		}
	}

}
