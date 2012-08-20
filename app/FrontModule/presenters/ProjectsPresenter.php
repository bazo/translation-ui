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
