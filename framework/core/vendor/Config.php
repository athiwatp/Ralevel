<?php

namespace JayaInstitute;

class Config {

	protected static $items;

	public function __construct()
	{
		$items = require 'framework/app/config.php';

		static::$items = $items;
	}

	public function get($key)
	{
		if (array_key_exists($key, static::$items)) return static::$items[$key];
		return '';
	}

	public function set($key, $value='')
	{
		static::$items[$key] = $value;
	}
}