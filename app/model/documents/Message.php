<?php

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Message
 *
 * @author martin.bazik
 * @ODM\Document
 */
class Message
{

	/**
	 * @ODM\Id
	 */
	private $id;

	/** @ODM\ReferenceOne(targetDocument="Translation") */
	private $translation;

	/**
	 * @ODM\String
	 * @ODM\Index
	 */
	private $context;

	/**
	 * @ODM\String
	 * @ODM\Index
	 */
	private $singular;

	/**
	 * @ODM\String
	 * @ODM\Index
	 */
	private $plural;

	/**
	 * @ODM\Int
	 */
	private $pluralsCount;

	/**
	 * @ODM\Collection
	 * @ODM\Index
	 */
	private $translations = [];

	/**
	 * @ODM\Boolean
	 * @ODM\Index
	 */
	private $translated = FALSE;


	public function getId()
	{
		return $this->id;
	}


	/**
	 * @return \Translation
	 */
	public function getTranslation()
	{
		return $this->translation;
	}


	/**
	 * @param \Translation $translation
	 * @return \Message
	 */
	public function setTranslation(\Translation $translation)
	{
		$this->translation = $translation;
		return $this;
	}


	public function getContext()
	{
		return $this->context;
	}


	public function setContext($context)
	{
		$this->context = $context;
		return $this;
	}


	public function getSingular()
	{
		return $this->singular;
	}


	public function setSingular($singular)
	{
		$this->singular = $singular;
		return $this;
	}


	public function hasPlural()
	{
		return $this->plural !== NULL;
	}


	public function getPlural()
	{
		return $this->plural;
	}


	public function setPlural($plural)
	{
		$this->plural = $plural;
		return $this;
	}


	public function getPluralsCount()
	{
		return $this->pluralsCount;
	}


	public function setPluralsCount($pluralsCount)
	{
		$this->pluralsCount = $pluralsCount;
		return $this;
	}


	public function getTranslations()
	{
		return $this->translations;
	}


	public function setTranslations(array $translations)
	{
		$this->translations = $translations;

		$translated = TRUE;

		foreach ($translations as $translation) {
			if ($translation === '' or $translation === NULL) {
				$translated = FALSE;
				break;
			}
		}
		$this->translated = $translated;
		return $this;
	}


	public function addTranslation($form, $translation)
	{
		$this->translations[$form] = $translation;
		return $this;
	}


	public function isTranslated()
	{
		return $this->translated;
	}


}

