<?php

/**
 * Compares each file's language keys, keeping the order and text of the master
 * language, producing a detailed report of missing keys at the same time.
 */
class LbStepKeyrefactor extends LbAbstractPart
{
	private $rootDir = null;
	private $subdirs = null;
	private $masterLang = null;
	private $transLang = null;
	private $overwrite = false;
	private $produceReport = false;
	private $reportfile = null;
	private $ignoreKeys = array();

	/**
	 * Initialisation of this step
	 */
	protected function _prepare()
	{
		$this->rootDir = $this->_parametersArray['root'];
		$this->subdirs = $this->_parametersArray['subdirs'];
		$this->masterLang = $this->_parametersArray['langs']['master'];
		$this->transLang = $this->_parametersArray['langs']['trans'];
		$this->overwrite = $this->_parametersArray['overwrite'];
		$this->produceReport = $this->_parametersArray['producereport'];

		if (array_key_exists('ignorekeys', $this->_parametersArray))
		{
			$this->ignoreKeys = $this->_parametersArray['ignorekeys'];
		}
		else
		{
			include __DIR__ . '/../skipkeys.php';
			$this->ignoreKeys = $skipkeys;
		}

		if ($this->produceReport)
		{
			$this->reportfile = $this->rootDir . '/report.txt';
			if (file_exists($this->reportfile))
			{
				unlink($this->reportfile);
			}
			/**
			if (file_exists($this->reportfile . '.php'))
			{
				unlink($this->reportfile . '.php');
			}
			/**/
		}

		$this->setState('prepared');
	}

	/**
	 * The main business logic of this step
	 */
	protected function _run()
	{
		echo "CHECKING LANGUAGE STRINGS\n";

		$totalMissing = 0;

		foreach ($this->subdirs as $subdir)
		{
			echo "\tScanning $subdir\n";

			// Determine the paths
			$masterPath = $this->rootDir . '/' . $this->masterLang . '/' . $subdir;
			$transPath = $this->rootDir . '/' . $this->transLang . '/' . $subdir;

			// List files
			$transFiles = $this->listFiles($transPath);

			foreach ($transFiles as $filename)
			{
				echo "\t\t$filename\n";

				// Get the paths to the master and translation file
				$parts = explode('.', $filename);
				array_shift($parts);

				$masterFilepath = $masterPath . '/' . $this->masterLang . '.' . implode('.', $parts);
				$transFilepath = $transPath . '/' . $filename;

				// Load the two files
				$lMaster = new LbUtilLang();
				$lMaster->load($masterFilepath);

				$lTrans = new LbUtilLang();
				$lTrans->load($transFilepath);

				// Go through the master lines
				$output = '';
				$missingKeys = array();
				$masterLines = $lMaster->getLines();

				foreach($masterLines as $line)
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

					$trans = $lTrans->_($key);
					if ((($trans == $key) || ($trans == $lMaster->_($key))))
					{
						// Untranslated string
						if (!in_array($key, $this->ignoreKeys))
						{
							$missingKeys[] = $key;
							$totalMissing++;
						}
						$output .= $line . "\n";
					}
					else
					{
						$output .= $key . '="';
						$x = str_replace('"\""', '"_QQ_"', $trans);
						$output .= str_replace('"', '"_QQ_"', $x);
						$output .= '"' . "\n";
					}
				}

				// Write to report
				if (!empty($missingKeys) && $this->produceReport)
				{
					$fp = fopen($this->reportfile, 'at');
					fwrite($fp, "\n" . $subdir . '/' . $filename . "\n");
					foreach ($missingKeys as $key)
					{
						fwrite($fp, str_pad($key, 70) . "\t" . $lMaster->_($key) . "\n");
					}
					fclose($fp);
				}

				// Write to report (DEBUG)
				/**
				if (!empty($missingKeys))
				{
					$fp = fopen($this->reportfile . '.php', 'at');
					foreach ($missingKeys as $key)
					{
						fwrite($fp, "'$key',\n");
					}
					fclose($fp);
				}
				/**/

				// Output file
				if ($this->overwrite)
				{
					unlink($transFilepath);
					file_put_contents($transFilepath, $output);
				}
			}
		}

		echo "\n\tTOTAL MISSING KEYS: $totalMissing\n";

		$this->setState('postrun');
	}

	/**
	 * Finalisation of this step
	 */
	protected function _finalize()
	{

		$this->setState('finished');
	}

	/**
	 * List all the INI files inside a directory
	 *
	 * @param   string  $dir  The directory to scan
	 *
	 * @return  array  A list of INI files (without the path)
	 */
	private function listFiles($dir)
	{
		$files = array();

		$dh = new DirectoryIterator($dir);

		foreach ($dh as $dirinfo)
		{
			if (!$dirinfo->isFile())
			{
				continue;
			}

			$filename = $dirinfo->getFilename();

			if (substr($filename, -4) != '.ini')
			{
				continue;
			}

			$files[] = $filename;
		}

		return $files;
	}

}