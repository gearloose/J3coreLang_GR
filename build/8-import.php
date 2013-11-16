<?php

// Load the factory (and autoloader)
require_once __DIR__ . '/engine/factory.php';

$steps = array(
	'import',
);

LbFactory::nuke();

$configArray = array(
	'root'			 => realpath(__DIR__ . '/..'),
	'subdirs'		 => array(
		'admin',
		'site',
		'install',
	),
	'langs'			 => array(
		'master' => 'en-GB',
		'trans'	 => 'el-GR',
	),
	'overwrite'		 => true,
	'producereport'	 => false,
);

echo "\n\n";

foreach ($steps as $stepName)
{
	$step	 = LbFactory::getStep($stepName);
	$step->setup($configArray);
	$done	 = false;

	while (!$done)
	{
		$ret = $step->tick();

		if ($ret['Error'])
		{
			echo "\n\n";

			echo str_repeat('*', 79) . "\n";
			echo "E R R O R\n";
			echo str_repeat('*', 79) . "\n";
			echo $ret['Error'] . "\n";
			die();
		}
		elseif (!$ret['HasRun'])
		{
			$done = true;
		}
	}
}