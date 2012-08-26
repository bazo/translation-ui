<?php
namespace FrontModule;
use Nette\Application\UI\Form;

use \AllowedLangs;
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
		
		$langs = AllowedLangs::getLangs();
		
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
			$translation = $this->context->projectFacade->createTranslation($this->project, $values->lang);
			$this->log($this->project, \Activity::CREATE_TRANSLATION, $translation);
			$this->flash(sprintf('Translation for language %s created', AllowedLangs::getLangCaption($values->lang)));
		}
		catch(\ExistingTranslationException $e)
		{
			$this->flash($e->getMessage(), 'error');
		}
		catch(\InvalidPluralRuleException $e)
		{
			$this->flash($e->getMessage(), 'error');
		}
		
		$this->redirect('this');
	}
	
	protected function createComponentFormImportTemplate()
	{
		if($this->acl->isAllowed($this->me, $this->project, 'importTemplate'))
		{
			$form = new Form;

			$form->addUpload('template', 'Template file')->setRequired();
			$form->addSubmit('btnSubmit', 'Import');

			$form->onSuccess[] = callback($this, 'formImportTemplateSubmitted');

			return $form;
		}
	}

	public function formImportTemplateSubmitted(Form $form)
	{
		$values = $form->getValues();
		
		if($values->template->isOk())
		{
			try
			{
				$neon = file_get_contents($values->template->getTemporaryFile());
				$data = \Nette\Utils\Neon::decode($neon);
				$imported = $this->context->projectFacade->importTemplate($data, $this->project);
				$status = $imported > 0 ? 'success' : 'error';
				$this->flash(sprintf('%d messages imported.', $imported), $status);
				if($imported > 0)
				{
					$this->log($this->project, \Activity::IMPORT_TEMPLATE, $imported);
				}
			}
			catch(\Nette\Utils\NeonException $e)
			{
				$this->flash(sprintf('Template contains illegal characters: %s', $e->getMessage()), 'error');
			}
			catch(\Nette\Utils\TokenizerException $e)
			{
				$this->flash('Uploaded file is not a valid template. Please upload a valid template.', 'error');
			}
		}
		else
		{
			$this->flash('Template file has not uploaded succesfully. Please try again.', 'error');
			
		}
		$this->redirect('this');
	}

	public function createComponentFormDelete()
	{
		if($this->acl->isAllowed($this->me, $this->project, 'danger'))
		{
			$form = new Form;

			$form->addSubmit('btnSubmit', 'Delete');

			$form->onSuccess[] = callback($this, 'formDeleteSubmitted');

			return $form;
		}
	}
	
	public function formDeleteSubmitted(Form $form)
	{
		if($this->me === $this->project->getOwner())
		{
			$this->context->projectFacade->delete($this->project);
			$this->flash(sprintf('Project %s was successfully deleted', $this->project->getCaption()));

			$this->log($this->project, \Activity::DELETE_PROJECT);
		}
		$this->redirect('projects:');
	}
	
	protected function createComponentFormInviteCollaborator()
	{
		$form = new Form;
		$form->addText('search', 'Search');
		$form->addHidden('id');
		
		$form->addSubmit('btnTranslate', 'Translate')->onClick[] = callback($this, 'formInviteCollaboratorBtnTranslateClicked');
		$form->addSubmit('btnAdmin', 'Admin')->onClick[] = callback($this, 'formInviteCollaboratorBtnAdminClicked');
		
		return $form;
	}
	
	public function handleSearch($query)
	{
		$result = array();
		$users = $this->context->userFacade->search($query, array($this->me->getId()));
		
		foreach($users as $user)
		{
			$result[$user->getId()] = array(
				'id' => $user->getId(),
				'nick' => $user->getNick(),
				'email' => $user->getEmail(),
				'gravatar' => $user->getGravatar()
			);
		}
		
		$this->sendResponse(new \Nette\Application\Responses\JsonResponse($result));
	}
	
	public function formInviteCollaboratorBtnTranslateClicked(\Nette\Forms\Controls\Button $button)
	{
		$values = $button->getForm()->getValues();
		$id = $values->id;
		
		$user = $this->context->userFacade->find($id);
		
		$level = \Access::TRANSLATOR;
		
		$access = $this->context->projectFacade->addCollaboratorToProject($user, $this->project, $level);
		$this->log($this->project, \Activity::ADD_COLLABORATOR, $access);
		
		$this->flash(sprintf('User <strong>%s</strong> has been added to project <strong>%s</strong> as %s', $user->getNick(), $this->project->getCaption(), $level));
		$this->redirect('this');
	}
	
	public function formInviteCollaboratorBtnAdminClicked(\Nette\Forms\Controls\Button $button)
	{
		$values = $button->getForm()->getValues();
		$id = $values->id;
		
		$user = $this->context->userFacade->find($id);
		
		$level = \Access::ADMIN;
		
		$access = $this->context->projectFacade->addCollaboratorToProject($user, $this->project, $level);
		$this->log($this->project, \Activity::ADD_COLLABORATOR, $access);
		
		$this->flash(sprintf('User <strong>%s</strong> has been added to project %s as %s', $user->getNick(), $this->project->getCaption(), $level));
		$this->redirect('this');
	}

}
