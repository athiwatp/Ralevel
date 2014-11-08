<?php

namespace JayaInstitute;

class Route {

	protected static $routes;

	public function add($path, $name, $target)
	{
		static::$routes[$name] = array('path' => $path, 'target' => $target); 		
	}

	public function start()
	{
		$uri = explode('/', \Uri::get());
		$routes = static::$routes;

		$getName = '';
		foreach ($routes as $name => $route) {
			$regex = 0;
			$nonRegex = 0;
			$path = $route['path'];
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

			if ($uriRegex == $regex && $uriNonRegex == $nonRegex) 
			{
				$getName = $name;
				continue;
			} 
			
		}


		if ($getName == '') return show_404();

		$getTarget = explode('@', $routes[$getName]['target']);

		switch (count($vars)) 
		{
			case 0: return $getTarget[0]::$getTarget[1]();
				break;

			case 1: return $getTarget[0]::$getTarget[1]($vars[0]);
				break;

			case 2: return $getTarget[0]::$getTarget[1]($vars[0], $vars[1]);
				break;

			case 3: return $getTarget[0]::$getTarget[1]($vars[0], $vars[1], $vars[2]);
				break;

			case 4: $getTarget[0]::$getTarget[1]($vars[0], $vars[1], $vars[2], $vars[3]);
				break;

			case 5: return $getTarget[0]::$getTarget[1]($vars[0], $vars[1], $vars[2], $vars[3], $vars[4]);
				break;

			case 6: return $getTarget[0]::$getTarget[1]($vars[0], $vars[1], $vars[2], $vars[3], $vars[4], $vars[5]);
				break;

			case 7: return $getTarget[0]::$getTarget[1]($vars[0], $vars[1], $vars[2], $vars[3], $vars[4], $vars[5], $vars[6]);
				break;
			
		}


	}

}

