<?php

class LbUtilLang
{
	private $strings = array();
	private $lines = array();

	public function load($filename)
	{
		$contents = file_get_contents($filename);

		$this->lines = array();
		$lines = explode("\n", $contents);

		foreach ($lines as $line)
		{
			$this->lines[] = rtrim($line);
		}

		$contents = str_replace('_QQ_', '"\""', $contents);
		$strings = @parse_ini_string($contents);

		if (!is_array($strings))
		{
			$strings = array();
		}

		$this->strings = $strings;
	}

	public function _($key)
	{
		$key = strtoupper($key);

		if (array_key_exists($key, $this->strings))
		{
			return $this->strings[$key];
		}
		else
		{
			return $key;
		}
	}

	public function &getLines()
	{
		return $this->lines;
	}
}