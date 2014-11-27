<?php

abstract class Model {

	protected static $booted;

	protected static function getLoadedClass()
	{
		return get_called_class();
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
		$namespace = 'Ralevel\\Model';

		$path = APPPATH.'models/'.$instance.'.php';
		$code = file_get_contents($path);

		$code = str_replace('<?php', "namespace $namespace; ", $code);
		$code = preg_replace('/(extends).+(\{)+/', 'extends \JayaInstitute\Db {', $code);

		eval($code);

		$newInstance = '\\'.$namespace.'\\'.$instance;
		$newInstance = new $newInstance();

		return static::$booted[$instance] = $newInstance;
	}

	public function __call($method, $args)
	{	
		$instance = static::createNewInstance(static::getLoadedClass());

		return call_user_func_array(array($instance, $method), $args);
	}

}