<?php

namespace JayaInstitute;

class Redirect {

	protected static $location;

	public function to($uri)
	{
		static::$location = base_url($uri);
		return $this;
	}

	public function route($name)
	{
		return $this->to(\Route::getPath($name));
	}

	public function withErrors($var = '', $value=null)
	{
		if ($value == null) return $this->withErrors('error', $var);

		\Session::put('errorsVars', array($var => $value));

		return $this;
	}

	public function withInput()
	{
		return $this;
	}

	public function redirect()
	{
		if (!empty(static::$location)) header('location: '.static::$location);
	}
}