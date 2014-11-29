<?php

namespace JayaInstitute;

class Request {

	private static $uri; 

	public function uri()
	{
		if (isset(static::$uri)) return static::$uri;
		return static::$uri = static::$uri = array_key_exists('REQUEST_URI', $_SERVER) ? str_replace(basename(\Url::baseUrl()).'/', '', (substr($_SERVER['REQUEST_URI'], 1))) : substr((isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO'), 1);
	}

	public function path()
	{
		return $this->uri();
	}

	public function method()
	{
		return (! is_null( \Input::get('_method') ) ) ? strtoupper(\Input::get('_method')) : $_SERVER['REQUEST_METHOD'];
	}

	public function isMethod($method)
	{
		if ($this->method() == $method) return true;
		return false;
	}

	public function url()
	{
		return base_url($this->path());
	}

	public function segment($index = null)
	{
		if ($index == null OR !is_numeric($index)) return $this->path();

		$segment = explode('/', $this->path());

		return $segment[$index];
	}

	public function ip()
	{
		return $_SERVER['REMOTE_ADDR'];
	}
}
