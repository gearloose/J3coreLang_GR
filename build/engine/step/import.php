<?php

class LbStepImport extends LbAbstractPart
{
	private $rootDir = null;
	private $subdirs = null;
	private $masterLang = null;
	private $transLang = null;
	private $overrides = array();

	/**
	 * Initialisation of this step
	 */
	protected function _prepare()
	{
		$this->rootDir = $this->_parametersArray['root'];
		$this->subdirs = $this->_parametersArray['subdirs'];
		$this->masterLang = $this->_parametersArray['langs']['master'];
		$this->transLang = $this->_parametersArray['langs']['trans'];

		$this->overrides = $this->getOverridesFromReport();

		$this->setState('prepared');
	}

	/**
	 * The main business logic of this step
	 */
	protected function _run()
	{
		echo "IMPORTING TRANSLATIONS FROM report.txt\n";

		if (empty($this->overrides))
		{
			$this->setState('postrun');

			return;
		}

		// Determine the main path
		$transPath = $this->rootDir . '/' . $this->transLang;
		$masterPath = $this->rootDir . '/' . $this->masterLang;

		foreach ($this->overrides as $file => $overrides)
		{
			// Get the path for the file
			$transFilePath = $transPath . '/' . $file;
			$masterFilepath = $masterPath . '/' . $file;
			$masterFilepath = str_replace($this->transLang, $this->masterLang, $masterFilepath);

			// Load the translation file
			$lTrans = new LbUtilLang();
			$lTrans->load($transFilePath);

			// Load the master file
			$mTrans = new LbUtilLang();
			$mTrans->load($masterPath);

			// Go through all translation lines and replace the overrides
			$transLines = $lTrans->getLines();
			$output = '';

			foreach($transLines as $line)
			{
				// Output empty lines verbatim
				if (empty($line))
				{
					$output .= $line . "\n";
					continue;
				}

				// Output non-translation lines verbatim
				if (!strstr($line, '="'))
				{
					$output .= $line . "\n";
					continue;
				}

				$firstChar = substr($line, 0, 1);

				$callItQuits = false;
				switch ($firstChar)
				{
					case ';':
					case '/':
					case '#':
						// Output comment lines verbatim
						$output .= $line . "\n";
						$callItQuits = true;
						break;
				}

				if ($callItQuits)
				{
					continue;
				}

				// Figure out the translation
				$parts = explode('=', $line, 2);
				$key = $parts[0];
				$value = $parts[1];

				$translated = false;

				if (array_key_exists($key, $overrides))
				{
					// Get overridden translation
					$t = $overrides[$key];

					// Get master language string
					$m = $mTrans->_($key);

					// Normalise original translated value
					$value = str_replace('"\""', '"_QQ_"', $value);
					$value = str_replace('"', '"_QQ_"', $value);

					// Normalise overridden value
					$t = str_replace('"\""', '"_QQ_"', $t);
					$t = str_replace('"', '"_QQ_"', $t);

					// Normalise master value
					$m = str_replace('"\""', '"_QQ_"', $m);
					$m = str_replace('"', '"_QQ_"', $m);

					// Make sure the override is not the same as the original translation or the master string
					if (($t != $m) && ($t != $value))
					{
						// Write override to the file
						$output .= $key . '="' . $t . '"' . "\n";
					}

					$translated = true;
				}

				if (!$translated)
				{
					$output .= $line . "\n";
				}
			}

			// Output file
			unlink($transFilePath);
			file_put_contents($transFilePath, $output);
		}

		$this->setState('postrun');
	}

	/**
	 * Finalisation of this step
	 */
	protected function _finalize()
	{

		$this->setState('finished');
	}

	protected function getOverridesFromReport()
	{
		$ret = array();
		$currentFile = '';

		$fp = fopen($this->rootDir . '/report.txt', 'rt');

		if ($fp === false)
		{
			return $ret;
		}

		while (!feof($fp))
		{
			$line = fgets($fp);
			$line = trim($line);

			if (empty($line))
			{
				continue;
			}

			$spacePos = strpos($line, ' ');

			if ($spacePos === false)
			{
				$currentFile = $line;
				$ret[$currentFile] = array();

				continue;
			}

			list($key, $value) = explode(' ', $line, 2);
			$value = ltrim($value);
			$ret[$currentFile][$key] = $value;
		}

		fclose ($fp);

		return $ret;
	}
}