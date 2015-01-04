<?php



/**
 * @author Martin Bažík <martin@bazo.sk>
 */
class KdybyTranslationBuilder
{

	public function build($fileMask, $data)
	{
		$messagesByDomain = [];
		foreach ($data['messages'] as $index => $messageData) {
			$message = $messageData['singular'];
			$domain = $messageData['context'];

			$translations = array_filter($messageData['translations']);
			if (empty($translations)) {
				continue;
			}

			if(count($translations) === 1) {
				$messagesByDomain[$domain][$message] = current($translations);
			} else {
				$messagesByDomain[$domain][$message] = implode('|', $messageData['translations']);
			}
		}
		return $this->buildLanguageFiles($fileMask, $messagesByDomain);
	}


	public function buildLanguageFiles($fileMask, $messagesByDomain)
	{
		$outputFiles = [];
		foreach ($messagesByDomain as $domain => $messages) {
			$data = [];
			foreach ($messages as $message => $translation) {
				$data[$message] = $translation;
			}

			$file = sprintf($fileMask, $domain);

			$outputFiles[$file] = $data;
		}

		return $outputFiles;
	}


}