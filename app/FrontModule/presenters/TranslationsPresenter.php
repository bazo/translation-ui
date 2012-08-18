<?php
namespace FrontModule;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;
/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyProjectlication
 */
class TranslationsPresenter extends SecuredPresenter
{
	public function renderDefault()
	{
		//$this->template->translations = $this->context->translationFacade->findAllTranslationsForUser($this->me);
	}
}
