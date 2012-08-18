<?php
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
/**
 * Log
 *
 * @author martin.bazik
 * @ODM\Document
 */
class TranslationComment
{
	private 
		/** 
		 * @ODM\Id 
		 */	
		$id,
		
		/**
		 * @ODM\String
		 * @ODM\Index
		 */	
		$context,	
			
		/**
		 * @ODM\String
		 * @ODM\Index
		 */	
		$singular,
			
		/**
		 * @ODM\String
		 * @ODM\Index
		 */		
		$plural,
		
		/**
		 * @ODM\Int
		 */		
		$pluralsCount,	
			
		/**
		 * @ODM\Hash
		 * @ODM\Index
		 */			
		$translations,
		
		/**
		 * @ODM\Boolean
		 * @ODM\Index
		 */		
		$translated = false
	;
	
	public function getId()
	{
		return $this->id;
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
		return $this->plural !== null;
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

	public function setTranslations($translations)
	{
		$this->translations = $translations;
		
		$translated = true;
		
		foreach($translations as $translation)
		{
			if($translation === '')
			{
				$translated = false;
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


}