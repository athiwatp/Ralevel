<?php

if ( ! function_exists('show_404')) 
{
	function show_404()
	{
		echo 'Error 404; Salah Routing...';
	}
}

if ( ! function_exists('app')) 
{
	function app($name)
	{
		if ($instance = Facade::getApp($name)) return $instance;
		else return $name::callMe();
	}
}

if ( ! function_exists('value')) 
{
	function value($value)
	{
		if (is_object($value)) return $value();
		return $value;
	}
}

if ( ! function_exists('is_stringify')) 
{
	function is_stringify($value)
	{
		if (is_object($value) && method_exists($value, 'toString') && is_string($value->toString())) return true;
		return false;
	}
}

if ( ! function_exists('base_url')) 
{
	function base_url($path = '')
	{
		return app('Url')->baseUrl().$path;
	}
}

if ( ! function_exists('storage_path')) 
{
	function storage_path($path = '')
	{
		return APPPATH.'storage/'.$path.'/';
	}
}



if ( ! function_exists('class_basename'))
{
	/**
	 * Get the class "basename" of the given object / class.
	 *
	 * @param  string|object  $class
	 * @return string
	 */
	function class_basename($class)
	{
		$class = is_object($class) ? get_class($class) : $class;

		return basename(str_replace('\\', '/', $class));
	}
}

if ( ! function_exists('e'))
{
	/**
	 * Escape HTML entities in a string.
	 *
	 * @param  string  $value
	 * @return string
	 */
	function e($value)
	{
		return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
	}
}

if ( ! function_exists('asset'))
{
	/**
	 * Generate an asset path for the application.
	 *
	 * @param  string  $path
	 * @param  bool    $secure
	 * @return string
	 */
	function asset($path)
	{
		return 'assets';
	}
}

if ( ! function_exists('array_except'))
{
	 
	function array_except($array, $keys)
	{
		return array_diff_key($array, array_flip((array) $keys));
	}
}

if ( ! function_exists('action'))
{
	 
	function action($name, $params = null)
	{
		return base_url(app('Route')->getPath($name, $params));
	}
}

if ( ! function_exists('current_route'))
{
	 
	function current_route()
	{
		return app('Route')->getCurrentRoute();
	}
}
