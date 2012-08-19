<?php
namespace Responses;

use Nette;

/**
 * Data download response.
 *
 * @author     Martin Bazik
 *
 * @property-read string $data
 * @property-read string $name
 * @property-read string $contentType
 */
class TextDownloadResponse extends Nette\Object implements Nette\Application\IResponse
{
	/** @var string */
	private $data;

	/** @var string */
	private $contentType;
	
	/** @var string */
	private $charset;

	/** @var string */
	private $name;

	/** @var bool */
	public $resuming = TRUE;


	/**
	 * @param  string  data 
	 * @param  string  imposed data name
	 * @param  string  MIME content type
	 */
	public function __construct($data, $name = NULL, $contentType = NULL, $charset = 'utf-8')
	{
		$this->data = $data;
		$this->name = $name;
		$this->contentType = $contentType ? $contentType : 'application/octet-stream';
		$this->charset = $charset;
	}



	/**
	 * Returns the path to a downloaded data.
	 * @return string
	 */
	final public function getData()
	{
		return $this->data;
	}



	/**
	 * Returns the data name.
	 * @return string
	 */
	final public function getName()
	{
		return $this->name;
	}



	/**
	 * Returns the MIME content type of a downloaded data.
	 * @return string
	 */
	final public function getContentType()
	{
		return $this->contentType;
	}



	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		if($this->charset !== null)
		{
			$httpResponse->setContentType($this->contentType);
			$httpResponse->setContentType($this->contentType.';charset='.$this->charset);
		}
		else
		{
			$httpResponse->setContentType($this->contentType);
		}
		
		$httpResponse->setHeader('Content-Disposition', 'attachment; filename="' . $this->name . '"');

		$length = strlen($this->data);
		$httpResponse->setHeader('Content-Length', $length);
		echo $this->data;
	}

}
