<?php
/**
 * A factory-like class to handle Singleton instances of various UNITE classes
 */
final class LbFactory
{
	/** @var array A list of instanciated objects */
	private $objectlist = array();

	/** @var array A list of instanciated objects based on keyed parameters */
	private $keyedobjectlist = array();

	/**
	 * Gets a single, internally used instance of the Factory
	 * @param string $serialized_data [optional] Serialized data to spawn the instance from
	 * @return UFactory A reference to the unique Factory object instance
	 */
	private static function &getInstance()
	{
		static $myInstance = null;

		if (!is_object($myInstance))
		{
			$myInstance = new self();
		}

		return $myInstance;
	}

	/**
	 * Internal function which instanciates a class named $class_name.
	 * @param string $class_name
	 * @return UAbstractObject
	 */
	public static function &getClassInstance($class_name)
	{
		$self = self::getInstance();
		if (!isset($self->objectlist[$class_name]))
		{
			$self->objectlist[$class_name] = new $class_name;
		}
		return $self->objectlist[$class_name];
	}

	/**
	 * Internal function which removes a class named $class_name
	 * @param string $class_name
	 */
	public static function &unsetClassInstance($class_name)
	{
		$self = self::getInstance();
		if (isset($self->objectlist[$class_name]))
		{
			$self->objectlist[$class_name] = null;
			unset($self->objectlist[$class_name]);
		}
	}

	/**
	 * Internal function which returns an instance of class $class_name initialized
	 * with $parameters.
	 *
	 * @param $class_name string The class name to spawn
	 * @param $parameters array Parameters array to pass to the class' constructor
	 * @return UAbstractObject
	 */
	public static function &getKeyedClassInstance($class_name, $parameters)
	{
		$self = self::getInstance();

		$key = md5(serialize($parameters));
		if (!array_key_exists($class_name, $self->keyedobjectlist))
		{
			$self->keyedobjectlist[$class_name] = array();
		}
		if (!array_key_exists($key, $self->keyedobjectlist[$class_name]))
		{
			$self->keyedobjectlist[$class_name][$key] = new $class_name($parameters);
		}

		return $self->keyedobjectlist[$class_name][$key];
	}

	public static function &unsetKeyedClassInstance($class_name, $parameters)
	{
		$self = self::getInstance();

		$key = md5(serialize($parameters));
		if (!array_key_exists($class_name, $self->keyedobjectlist))
		{
			return;
		}
		if (!array_key_exists($key, $self->keyedobjectlist[$class_name]))
		{
			unset($self->keyedobjectlist[$class_name][$key]);
		}
		if (empty($self->keyedobjectlist[$class_name]))
		{
			unset($self->keyedobjectlist[$class_name]);
		}
	}

	/**
	 * Reset the internal factory state, freeing all previosuly created objects
	 */
	public static function nuke()
	{
		$self = self::getInstance();
		foreach ($self->objectlist as $key => $object)
		{
			$self->objectlist[$key] = null;
		}
		$self->objectlist = array();
	}

	/**
	 * Gets a reference to the global scripting object
	 * @return UCoreScripting
	 */
	public static function getScripting()
	{
		return self::getClassInstance('LbCoreScripting');
	}

	/**
	 * Returns a step object
	 * @param $part string The part class name, without the UStep part
	 * @return UAbstractPart
	 */
	public static function getStep($part)
	{
		$partClass = 'LbStep' . ucfirst($part);
		return self::getClassInstance($partClass);
	}

	/**
	 * Destroys a step object
	 * @param $part string The part class name, without the UStep part
	 * @return UAbstractPart
	 */
	public static function unsetStep($part)
	{
		$partClass = 'LbStep' . ucfirst($part);
		self::unsetClassInstance($partClass);
	}

}

/**
 * Registers our class autoloader function
 * @param $class string The class to load
 */
function __autoload($class)
{
	// Get a reference to UNITE's engine root
	static $root = null;

	if (is_null($root))
	{
		$root = __DIR__;
	}

	// Special case: the UConfig configuration class is stored one level above
	if ($class == 'LbConfig')
	{
		require_once "$root/../config.php";
		return;
	}

	// Convert USomeClass to u.some.class
	$dotNotation = strtolower(preg_replace("/(([a-z])([A-Z])|([A-Z])([A-Z][a-z]))/", "\\2\\4.\\3\\5", $class));

	// Handle only UNITE classes
	if (substr($dotNotation, 0, 3) != 'lb.')
	{
		return;
	}

	// Create a file path from the class name
	$expanded	 = explode('.', substr($dotNotation, 2));
	$file		 = implode('/', $expanded);

	// Try to find first a match in plugins, then a match in the main core classes
	$check1	 = "$root/plugins/$file.php";
	$check2	 = "$root/$file.php";

	if (file_exists($check1))
	{
		require_once $check1;
	}
	elseif (file_exists($check2))
	{
		require_once $check2;
	}
}