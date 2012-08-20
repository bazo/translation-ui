<?php
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
/**
 * SystemLog
 *
 * @author martin.bazik
 * @ODM\Document
 */
class SystemLog
{

	private
			/**
			 * @ODM\Id
			 */
			$id,
			
			/**
			 * @ODM\String
			 */
			$message,
			
			/**
			 * @ODM\Date
			 * @ODM\Index(order="desc")
			 */
			$time,
			
			/**
			 * @ODM\String
			 */
			$who
	;

	function __construct($who = null, $message = null)
	{
		$this->time = new DateTime;
		$this->message = $message;
		$this->who = $who;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}

	public function getWho()
	{
		return $this->who;
	}

	public function setWho($who)
	{
		$this->who = $who;
		return $this;
	}
	
	public function getTime()
	{
		return $this->time;
	}
}