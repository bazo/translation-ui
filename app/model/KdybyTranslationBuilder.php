<?php



/**
 * @author Martin Bažík <martin@bazo.sk>
 */
class KdybyTranslationBuilder
{

	public function build($fileMask, $data, $defaultDomain)
	{
		$messagesByDomain = [];
		foreach ($data['messages'] as $index => $messageData) {
			$message = $messageData['singular'];
			if (strpos($message, '.') !== FALSE && strpos($message, ' ') === FALSE) {
				list($domain, $message) = explode('.', $message, 2);
			} else {
				$domain = $defaultDomain;
			}

			$messagesByDomain[$domain][$message] = current($messageData['translations']);
		}
		return $this->buildLanguageFiles($fileMask, $messagesByDomain);
	}


	public function buildLanguageFiles($fileMask, $messagesByDomain)
	{
		$outputFiles = [];
		foreach ($messagesByDomain as $domain => $messages) {
			$data = [];
			foreach ($messages as $message) {
				$data[$message] = $message;
			}

			$file = sprintf($fileMask, $domain);

			$outputFiles[$file] = $data;
		}

		return $outputFiles;
	}


}