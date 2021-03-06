<?php

abstract class Facade {

	protected static $resolvedInstance;

	protected static $app;

	public static function getApp($name)
	{
		if (isset(static::$app[$name])) return static::$app[$name];
	}

	public static function callMe()
	{
		$instanceName = static::getFacadeRoot();

		return static::$app[get_called_class()] = (is_object($instanceName)) ? $instanceName : new $instanceName();
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
		$instance = static::callMe();

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