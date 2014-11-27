<?php

namespace JayaInstitute;

class Uri {

	private static $uri; 

	public function __construct()
	{
		if ( ! isset(static::$uri)) 
			static::$uri = substr((isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO'), 1);
	}

	public function get()
	{
		return static::$uri;
	}

	public function requestMethod()
	{
		return $_SERVER['REQUEST_METHOD'];
	}


}