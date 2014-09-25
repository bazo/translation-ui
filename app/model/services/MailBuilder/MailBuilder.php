<?php

namespace Jobzine\Services;

/**
 * Description of MailBuilder
 *
 * @author Martin
 */
use Nette\Application\UI\Presenter,
	Nette\Templating\FileTemplate,
	Nette\Latte\Engine,
	Nette\Latte\Macros\MacroSet,
	Nette\Mail\Message

;

class MailBuilder
{

	private
		/** @var Presenter */
		$presenter,
		/** @var FileTemplate */
		$template,
		$from,
		$fromName,
		/** @var \Nette\Mail\IMailer */
		$mailer
	;

	/**
	 *
	 * @param Presenter $presenter 
	 */
	public function __construct(Presenter $presenter)
	{
		$this->presenter = $presenter;
		$this->from = $presenter->context->params['mail']['from'];
		$this->fromName = $presenter->context->params['mail']['fromName'];
		$this->template = $this->createTemplate($presenter);
		$this->mailer = $presenter->context->nette->mailer;
	}

	/**
	 *
	 * @param Presenter $presenter
	 * @return FileTemplate 
	 */
	private function createTemplate(Presenter $presenter)
	{
		$template = new FileTemplate();
		$latte = new Engine;
		$template->registerFilter($latte);
		$set = MacroSet::install($latte->getCompiler());
		$template->control = $template->presenter = $presenter;
		return $template;
	}

	/**
	 *
	 * @return Message 
	 */
	private function prepareMessage()
	{
		$message = new Message;
		$message->setFrom($this->from, $this->fromName);
		$message->setMailer($this->mailer);
		return $message;
	}

	/**
	 * Builds registration email message
	 * @param \User $user
	 * @return Message 
	 */
	public function buildRegistrationEmail(\User $user, \RegistrationToken $token)
	{
		$this->template->user = $user;
		$this->template->token = $token;
		$this->template->confirmLink = $this->presenter->link('//register:confirmation', array('token' => $token->getToken()));
		
		$this->template->setFile(__DIR__ . '/templates/registration.latte');
		
		$text = $this->template->__toString();
		
		$message = $this->prepareMessage();
		$message->addTo($user->getEmail());
		$message->setSubject('Mazagran registration');
		$message->setBody($text);
		return $message;
	}
	
	/**
	 * Builds registration email message
	 * @param \User $user
	 * @return Message 
	 */
	public function buildPasswordRecoveryEmail(\User $user, \PasswordRecoveryToken $token)
	{
		$this->template->user = $user;
		$this->template->token = $token;
		$this->template->confirmLink = $this->presenter->link('//jobzine:changePassword', array('token' => $token->getToken()));
		
		
		$this->template->setFile(__DIR__ . '/templates/recover_password.latte');
		
		$text = $this->template->__toString();
		
		$message = $this->prepareMessage();
		$message->addTo($user->getEmail());
		$message->setSubject(_('Obnovenie hesla'));
		$message->setBody($text);
		return $message;
	}

}