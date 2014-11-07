<?php

abstract class Model {

	protected static $booted;

	public static function __callStatic($method, $args)
	{
		$instance = get_called_class().'_boot';

		if (isset(static::$booted[$instance])) 
			return call_user_func_array(array(static::$booted[$instance], $method), $args);
		
		$vars = get_object_vars(new static($instance));
		$code = "namespace JayaInstitute; \r\n";
		$code = "class $instance extends \JayaInstitute\Db { \r\n";

		foreach ($vars as $key => $value) {
			$code .= "protected \$$key = '$value'; \r\n";
		}

		$code .= '}';

		eval($code);

		$newInstance = new $instance();

		static::$booted[$instance] = $newInstance;

		return call_user_func_array(array($newInstance, $method), $args);
	}

}