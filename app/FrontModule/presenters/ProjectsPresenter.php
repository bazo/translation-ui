<?php
namespace FrontModule;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;
use Mazagran\Translation\Langs;
/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyProjectlication
 */
class ProjectsPresenter extends SecuredPresenter
{

	protected function createComponentFormNewProject()
	{
		$form = new Form;
		
		$locales = Langs::getLangs();
		$langs = array();
		foreach($locales as $locale => $plural)
		{
			$langs[$locale] = $locale;
		}
		
		$form->addText('caption', 'Name')->setRequired();
		$form->addSelect('sourceLang', 'Source language', $langs)->setRequired();
		$form->addText('link', 'Link to web');
		$form->addSubmit('btnSubmit', 'Create');
		$form->onSuccess[] = callback($this, 'formNewProjectSubmitted');
		return $form;
		
	}
	
	public function formNewProjectSubmitted(Form $form)
	{
		$values = $form->getValues();
		
		try
		{
			$app = $this->context->projectFacade->createProject($values->caption, $this->me);
			$this->redirect('project:', array('id' => $app->getId()));
		}
		catch(\ExistingProjectException $e)
		{
			$this->flash($e->getMessage(), 'error');
		}
	}
	
	public function renderProject($id)
	{
		$this->template->app = $this->context->projectFacade->find($id);
	}
	
	public function handleDelete($id)
	{
		$this->context->projectFacade->delete($id);
		$this->flash('Project sucessfully removed', 'success');
		$this->redirect('apps:');
	}

}
