<?php
namespace FrontModule;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

use Mazagran\Translation\Langs;
/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ProjectPresenter extends SecuredPresenter
{
	public
		/** @persistent */	
		$id
	;
	
	private 
		/** @var \Project */	
		$project
	;

	protected function startup()
	{
		parent::startup();
		$this->id = $this->getParameter('id');
		$this->project = $this->context->projectFacade->find($this->id);
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		
		$this->template->project = $this->project;
	}
	
	protected function createComponentFormAddTranslation()
	{
		$form = new Form;
		
		$locales = Langs::getLangs();
		$langs = array();
		foreach($locales as $locale => $plural)
		{
			$langs[$locale] = $locale;
		}
		
		$form->addSelect('lang', 'Language', $langs);
		$form->addSubmit('btnSubmit', 'Create');
		
		$form->onSuccess[] = callback($this, 'formAddTranslationSubmitted');
		return $form;
	}
	
	public function formAddTranslationSubmitted(Form $form)
	{
		$values = $form->getValues();
		try
		{
			$this->context->projectFacade->createTranslation($this->project, $values->lang);
			$this->flash(sprintf('Translation for language %s created', $values->lang));
		}
		catch(\ExistingTranslationException $e)
		{
			$this->flash($e->getMessage(), 'error');
		}
		
		$this->redirect('this');
	}
	
	protected function createComponentFormImportTemplate()
	{
		$form = new Form;
		
		$form->addUpload('template', 'template')->setRequired();
		$form->addSubmit('btnSubmit', 'Import');

		$form->onSuccess[] = callback($this, 'formImportTemplateSubmitted');
		
		return $form;
	}

	public function formImportTemplateSubmitted(Form $form)
	{
		$values = $form->getValues();
		
		if($values->template->isOk())
		{
			$neon = file_get_contents($values->template->getTemporaryFile());
			$data = \Nette\Utils\Neon::decode($neon);
			$this->context->projectFacade->importTemplate($data, $this->project);
			
			$this->redirect('this');
		}
	}

	public function handleDelete($id)
	{
		$this->context->appService->delete($id);
		$this->flash('App sucessfully removed', 'success');
		$this->redirect('apps:');
	}

}
