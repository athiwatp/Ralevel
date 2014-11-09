<?php

abstract class Model {

	protected static $booted;

	protected static function getLoadedClass()
	{
		return get_called_class().'Model';
	}

	public function __get($key)
	{
		$instance = static::createNewInstance(static::getLoadedClass());
		
		return $instance->$key;
	}

	public function __set($key, $value)
	{
		$instance = static::createNewInstance(static::getLoadedClass());
		
		return $instance->$key = $value;
	}

	public static function __callStatic($method, $args)
	{
		$instance = static::createNewInstance(static::getLoadedClass());

		return call_user_func_array(array($instance, $method), $args);
	}

	protected static function createNewInstance($instance)
	{
		if (isset(static::$booted[$instance])) return static::$booted[$instance];
		$vars = get_object_vars(new static($instance));
		$code = "namespace JayaInstitute; \r\n";
		$code = "class $instance extends \JayaInstitute\Db { \r\n";

		foreach ($vars as $key => $value) {
			$code .= "protected \$$key = '$value'; \r\n";
		}

		$code .= '}';

		eval($code);

		$newInstance = new $instance();

		return static::$booted[$instance] = $newInstance;
	}

	public function __call($method, $args)
	{	
		$instance = static::createNewInstance(static::getLoadedClass());

		return call_user_func_array(array($instance, $method), $args);
	}

}