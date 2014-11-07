<?php

namespace JayaInstitute;

class Uri {

	private static $uri; 

	public function __construct()
	{
	}

	public function get()
	{
		return static::$uri = substr((isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO'), 1);
	}


}