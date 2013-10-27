<?php

namespace Forms;

use Nette;
use Nette\Application\UI\Form;
use Jobzine\Forms\Controls;

/**
 * Description of BaseForm
 *
 * @author martin.bazik
 */
class BaseForm extends Form
{

	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @param type $side
	 * @param type $border
	 * @return Controls\ColorPicker
	 */
	public function addColorPicker($name, $caption, $side = 30, $border = 3)
	{
		return $this[$name] = new Controls\ColorPicker($caption, $side, $border);
	}


	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @param type $min
	 * @param type $max
	 * @return type Controls\Slider
	 */
	public function addSlider($name, $caption, $min = 0, $max = 100)
	{
		return $this[$name] = new Controls\Slider($caption, $min, $max);
	}


	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @param type $min
	 * @param type $max
	 * @return type Controls\PlusMinus
	 */
	public function addPlusMinus($name, $caption, $min = 0, $max = 100)
	{
		return $this[$name] = new Controls\PlusMinus($caption, $min, $max);
	}


	/**
	 *
	 * @param type $name
	 * @param type $caption
	 * @param type $items
	 * @return type Controls\LinkedSelectBox
	 */
	public function addLinkedSelect($name, $caption, $items)
	{
		return $this[$name] = new Controls\LinkedSelectBox($caption, $items);
	}


	public function addDatePicker($name, $label)
	{
		return $this[$name] = new Controls\DatePicker($label);
	}


	/**
	 *
	 * @param string $name
	 * @param string $label
	 * @return Html5Input
	 */
	public function addEmail($name, $label = NULL)
	{
		$input = new \Nette\Forms\Controls\TextInput($label);
		$input->type = 'email';
		$input->addCondition(Form::FILLED)->addRule(Form::EMAIL, '%label must be a valid email');
		return $this[$name] = $input;
	}


	public function addCheckboxList($name, $label, $items = NULL)
	{
		$list = new Controls\CheckboxList($label, $items);
		return $this[$name] = $list;
	}


	public function addReCaptcha($name, $label, $publicKey, $privateKey)
	{
		$reCaptcha = new Controls\ReCaptcha($label, $publicKey, $privateKey);
		return $this[$name] = $reCaptcha;
	}


}

