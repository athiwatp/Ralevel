<?php

namespace JayaInstitute;

class Route {

	protected static $routes;

	protected static $filters;

	protected static $currentRoute;

	public function add($path, $name, $target = null, $method = 'get')
	{
		$path = array_map(function($v){return rtrim($v, '/');}, is_array($path) ? $path : array($path));

		if (is_array($name))
		{
			foreach ($name as $key => $value) {
				if (is_numeric($key))
				{
					$target = $value;
					continue;
				}  
				
				$$key = $value;
			}

		}

		$name = is_array($name) ? 'route_'.count(static::$routes) : $name;

		$before = (isset($before)) ? (is_array($before) ? $before : explode('|', $before)) : array();

		$after = (isset($after)) ? (is_array($after) ? $after : explode('|', $after)) : array();

		static::$routes[$name] = array('path' => $path, 'target' => $target, 'method' => strtoupper($method), 'before' => $before, 'after' => $after); 		
	}

	public function get($path, $name, $target)
	{
		return $this->add($path, $name, $target, 'get');
	}

	public function post($path, $name, $target)
	{
		return $this->add($path, $name, $target, 'post');
	}

	public function put($path, $name, $target)
	{
		return $this->add($path, $name, $target, 'put');
	}

	public function delete($path, $name, $target)
	{
		return $this->add($path, $name, $target, 'delete');
	}

	public function getPath($name, $params = null)
	{
		// get the first path in path array

		$path = isset(static::$routes[$name]) ? static::$routes[$name]['path'][0] : '';

		$i = 0;
		
		$slices = array();
		
		foreach (explode('/', $path) as $slice)
		{
			$test = (substr($slice, 0, 1) == '{' && substr($slice, -1, 1) == '}');
			$slices[] = ($test) ? (null !== $params[$i] ? $params[$i] : '') : $slice;
			
			if ($test) $i++; 
		}

		return implode('/', $slices);
	}

	public function getCurrentRoute()
	{
		return static::$currentRoute;
	}

	public function filter($name, $filter)
	{
		static::$filters[$name] = $filter;
	}

	public function callFilter($method, $params)
	{
		if (isset(static::$filters[$method]))
		{
			$func = static::$filters[$method];

			switch (count($params)) 
			{
				case 0: return $func(new \Route, new \Request);
					break;
	
				case 1: return $func(new \Route, new \Request, $params[0]);
					break;
	
				case 2: return $func(new \Route, new \Request, $params[0], $params[1]);
					break;
	
				case 3: return $func(new \Route, new \Request, $params[0], $params[1], $params[2]);
					break;
	
				case 4: return $func(new \Route, new \Request, $params[0], $params[1], $params[2], $params[3]);
					break;
	
				case 5: return $func(new \Route, new \Request, $params[0], $params[1], $params[2], $params[3], $params[4]);
					break;
	
				case 6: return $func(new \Route, new \Request, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
					break;
	
				case 7: return $func(new \Route, new \Request, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6]);
					break;
				
			}
		}		
	}

	public function when($path, $filter)
	{
		$routes = static::$routes;

		$filters = is_array($filter) ? $filter : explode('|', $filter);

		$paths = is_array($path) ? $path : array($path);

		foreach ($paths as $path) 
		{
			$pattern = '/^'.str_replace('*', '.+', addcslashes($path, '/')).'$/';
	
			foreach ($routes as $name => $route) 
			{
				foreach ($route['path'] as $pathRoute) 
				{
					if (preg_match($pattern, $pathRoute)) 
					{
						$route['before'] = array_merge($route['before'], $filters);
		
						static::$routes[$name] = $route;
					}
				}
			}
		}
	}

	public function start()
	{
		$uri = explode('/', \Request::path());
		$routes = static::$routes;

		$getName = '';
		foreach ($routes as $name => $route) {
			foreach ($route['path'] as $path) {
				$regex = 0;
				$nonRegex = 0;
				$paths = array();
				foreach(explode('/', $path) as $slice)
				{
					if (substr($slice, 0, 1) == '{' && substr($slice, -1, 1) == '}')
					{
						$regex++;
						$paths[] = 'regex';
						continue;
					}
	
					$nonRegex++;
					$paths[] = $slice;
				}
	
				if (count($uri) != count($paths)) continue;
	
				$uriRegex = 0;
				$uriNonRegex = 0;
				$vars = array();
				$c = 0;
				foreach ($uri as $slice) {
					if ($slice == $paths[$c] && $paths[$c] != 'regex') $uriNonRegex++;
					else 
					{
						$uriRegex++;
						$vars[] = $slice;
					}
					$c++;
				}
	
				if ($uriRegex == $regex && $uriNonRegex == $nonRegex && $routes[$name]['method'] == \Request::method()) 
				{
					$getName = $name;
					continue;
				} 
				
			}
		}

		if ($getName == '') return show_404();

		// if ($routes[$getName]['method'] != \Request::method()) return show_404();

		$before = $routes[$getName]['before'];


		foreach ($before as $value)
		{
			$splits = explode(':', $value);

			$method = $splits[0];

			$params = isset($splits[1]) ? $splits[1] : null;

			if ($this->callFilter($method, $params) instanceOf \JayaInstitute\Redirect) return \Redirect::redirect();
		}

		static::$currentRoute = $getName;

		$target = $routes[$getName]['target'];


		if (is_object($target))  $return = call_user_func_array($target, $vars);
		else
		{
			$getTarget = explode('@', $target);

			$return = call_user_func_array(array($getTarget[0], $getTarget[1]), $vars);
		}

		if ($return instanceOf \JayaInstitute\Redirect) return \Redirect::redirect();

		return $return;
	}

}

