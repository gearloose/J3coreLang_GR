<?php

/**
 * Compares the filesystem structures, removing obsolete files and copying over
 * the new files
 */
class LbStepFscompare extends LbAbstractPart
{
	private $rootDir = null;
	private $subdirs = null;
	private $masterLang = null;
	private $transLang = null;

	/**
	 * Initialisation of this step
	 */
	protected function _prepare()
	{
		$this->rootDir = $this->_parametersArray['root'];
		$this->subdirs = $this->_parametersArray['subdirs'];
		$this->masterLang = $this->_parametersArray['langs']['master'];
		$this->transLang = $this->_parametersArray['langs']['trans'];

		$this->setState('prepared');
	}

	/**
	 * The main business logic of this step
	 */
	protected function _run()
	{
		echo "CHECKING FOR ADDED AND DELETED FILES\n";

		foreach ($this->subdirs as $subdir)
		{
			echo "\tScanning $subdir\n";

			// Determine the paths
			$masterPath = $this->rootDir . '/' . $this->masterLang . '/' . $subdir;
			$transPath = $this->rootDir . '/' . $this->transLang . '/' . $subdir;

			// List files
			$masterFiles = $this->listFiles($masterPath);
			$transFiles = $this->listFiles($transPath);

			// Find out which files are added / removed
			$added = $this->array_diff($masterFiles, $transFiles);
			$deleted = $this->array_diff($transFiles, $masterFiles);

			// Copy added files
			if (!empty($added))
			{
				echo "\t\tAdding new files:\n";

				foreach ($added as $filename)
				{
					echo "\t\t\t$filename\n";

					copy($masterPath . '/' . $this->masterLang . '.' . $filename,
						$transPath . '/' . $this->transLang . '.' . $filename);
				}
			}

			// Delete removed files
			if (!empty($deleted))
			{
				echo "\t\tDeleting removed files:\n";

				foreach ($deleted as $filename)
				{
					echo "\t\t\t$filename\n";

					unlink($transPath . '/' . $this->transLang . '.' . $filename);
				}
			}
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

	/**
	 * Works like array_diff but ignores the language portion of the filename
	 *
	 * @param   array  $from  The source list of files
	 * @param   array  $to    The target list of files
	 *
	 * @return  array  The files in $from not appearing in $to without their language prefix
	 */
	private function array_diff($from, $to)
	{
		$tempFrom = array();

		foreach ($from as $file)
		{
			$parts = explode('.', $file);
			$fromLanguage = array_shift($parts);
			$tempFrom[] = implode('.', $parts);
		}

		$tempTo = array();

		foreach ($to as $file)
		{
			$parts = explode('.', $file);
			$toLanguage = array_shift($parts);
			$tempTo[] = implode('.', $parts);
		}

		$result = array_diff($tempFrom, $tempTo);

		return $result;
	}
}