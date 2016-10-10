<?php

/**
 * Compares each file's language keys, keeping the order and text of the master
 * language, producing a detailed report of missing keys at the same time.
 */
class LbStepPackage extends LbAbstractPart
{
	private $rootDir = null;
	private $subdirs = null;
	private $masterLang = null;
	private $transLang = null;
	private $overwrite = false;
	private $shortversion = null;
	private $version = null;
	private $jversion = null;
	private $date = null;

	/**
	 * Initialisation of this step
	 */
	protected function _prepare()
	{
		$this->rootDir = $this->_parametersArray['root'];
		$this->subdirs = $this->_parametersArray['subdirs'];
		$this->masterLang = $this->_parametersArray['langs']['master'];
		$this->transLang = $this->_parametersArray['langs']['trans'];

		$this->version = $this->_parametersArray['version'] . '.' .
			$this->_parametersArray['revision'];

		$this->jversion = $this->_parametersArray['version'];

		// Get the short version (3.1, 3.2, ...)
		$parts = explode('.', $this->version, 3);
		$this->shortversion = $parts[0] . '.' . $parts[1];

		$this->date = date('Y-m-d');

		$this->setState('prepared');
	}

	/**
	 * The main business logic of this step
	 */
	protected function _run()
	{
		echo "MAKING PACKAGE\n";

		$allZipFiles = array();

		foreach ($this->subdirs as $subdir)
		{
			echo "\tScanning $subdir\n";

			// Determine the paths
			$masterPath = $this->rootDir . '/' . $this->masterLang . '/' . $subdir;
			$transPath = $this->rootDir . '/' . $this->transLang . '/' . $subdir;

			// List files
			$transFiles = $this->listFiles($transPath);

			// Prepare the string used for ##INIFILES## replacement
			$iniFilesString = '';
			foreach ($transFiles as $filename)
			{
				$iniFilesString .= "\t\t<filename>$filename</filename>\n";
			}

			// Copy the XML files, replacing strings
			$xmlFiles = array(
				array(
					'source'	=> $this->rootDir . '/build/xml/' . $subdir . '.' . $this->transLang . '.xml',
					'target'	=> $transPath . '/' . $this->transLang . '.xml',
				),
				array(
					'source'	=> $this->rootDir . '/build/xml/' . $subdir . '.install.xml',
					'target'	=> $transPath . '/install.xml',
				),
			);

			foreach ($xmlFiles as $def)
			{
				$source = $def['source'];
				$target = $def['target'];

				$contents = @file_get_contents($source);

				if ($contents === false)
				{
					continue;
				}

				$contents = str_replace('##INIFILES##', $iniFilesString, $contents);
				$contents = str_replace('##VERSION##', $this->version, $contents);
				$contents = str_replace('##JVERSION##', $this->jversion, $contents);
				$contents = str_replace('##SHORTVERSION##', $this->shortversion, $contents);
				$contents = str_replace('##DATE##', $this->date, $contents);

				file_put_contents($target, $contents);
			}

			// Get the ZIP file's path
			$zipPath = $this->rootDir . '/release/' . $this->_parametersArray['packagenames'][$subdir] . '.zip';
			@unlink($zipPath);

			if ($subdir != 'install')
			{
				$allZipFiles[] = $zipPath;
			}

			// Generate ZIP file
			$zip = new ZipArchive();
			$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
			foreach (glob($transPath . '/*.{ini,php,xml,html,htm}', GLOB_BRACE) as $file)
			{
				$zip->addFile($file, basename($file));
			}
			$zip->close();
		}

		// Copy the master package XML manifest
		$sourceXML = $this->rootDir . '/build/xml/pkg_' . $this->transLang . '.xml';
		$targetXML = $this->rootDir . '/release/pkg_' . $this->transLang . '.xml';

		$contents = @file_get_contents($sourceXML);

		if ($contents !== false)
		{
			$contents = str_replace('##INIFILES##', $iniFilesString, $contents);
			$contents = str_replace('##VERSION##', $this->version, $contents);
			$contents = str_replace('##JVERSION##', $this->jversion, $contents);
			$contents = str_replace('##SHORTVERSION##', $this->shortversion, $contents);
			$contents = str_replace('##DATE##', $this->date, $contents);

			file_put_contents($targetXML, $contents);
		}

		// Generate master ZIP file
		$zipPath = $this->rootDir . '/release/' . $this->_parametersArray['packagenames']['full'] . '.zip';
		$zipPath = str_replace('[VERSION]', $this->_parametersArray['version'], $zipPath);
		$zipPath = str_replace('[REVISION]', $this->_parametersArray['revision'], $zipPath);
		$zipPath = str_replace('[DATE]', $this->date, $zipPath);
		@unlink($zipPath);

		$zip = new ZipArchive();
		$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		foreach ($allZipFiles as $file)
		{
			$zip->addFile($file, basename($file));
		}
		$zip->addFile($targetXML, basename($targetXML));
		$zip->close();

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