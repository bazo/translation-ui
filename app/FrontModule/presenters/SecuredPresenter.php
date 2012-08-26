<?php
namespace FrontModule;
use Nette\Application\UI\Form;
use \AllowedLangs;
/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class SecuredPresenter extends BasePresenter
{

	protected
		$subTabs = array(
		),
		
		/** @var Services\Authorizator */	
		$acl
	;		
	
	protected function startup()
	{
		parent::startup();
		if(!$this->user->isLoggedIn() or !$this->user->isInRole('user'))
		{
			$this->redirect('sign:in');
		}
		$this->acl = $this->context->authorizator;
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->parameters = $this->context->parameters;
		$this->template->acl = $this->acl;
	}
	
	public function handleLogout()
	{
		$this->user->logout(true);
		$this->redirect('sign:in');
	}
	
	protected function createComponentFormNewProject()
	{
		$form = new Form;
		
		$langs = AllowedLangs::getLangs();
		
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
		if($this->user->identity->canAddProject())
		{
			try
			{
				$app = $this->context->projectFacade->createProject($values, $this->me);
				$this->redirect('project:', array('id' => $app->getId()));
			}
			catch(\ExistingProjectException $e)
			{
				$this->flash($e->getMessage(), 'error');
			}
		}
		else
		{
			$this->flash('You cannot add more projects.', 'error');
			$this->redirect('this');
		}
	}

}
