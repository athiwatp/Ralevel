<?php

abstract class Facade {

	protected static $resolvedInstance;

	protected static $app;

	protected static function addFacadeApp()
	{
		static::$app[$name] = $app;
	}

	protected static function resolveFacadeInstance($name)
	{
		if (is_object($name)) return $name;

		if (isset(static::$resolvedInstance[$name]))
		{
			return static::$resolvedInstance[$name];
		}

		return static::$resolvedInstance[$name] = $name;
	}

	public static function getFacadeRoot()
	{

		return static::resolveFacadeInstance(static::getFacadeAccessor());
	}

	
	protected static function getFacadeAccessor()
	{
		throw new \RuntimeException("Facade does not implement getFacadeAccessor method.");
	}


	public static function __callStatic($method, $args)
	{

		$instanceName = static::getFacadeRoot();

		$instance = new $instanceName();

		switch (count($args))
		{
			case 0:
				return $instance->$method();

			case 1:
				return $instance->$method($args[0]);

			case 2:
				return $instance->$method($args[0], $args[1]);

			case 3:
				return $instance->$method($args[0], $args[1], $args[2]);

			case 4:
				return $instance->$method($args[0], $args[1], $args[2], $args[3]);

			default:
				return call_user_func_array(array($instance, $method), $args);
		}
	}


}