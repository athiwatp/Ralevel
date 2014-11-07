<?php


class Controller extends Facade {

	protected static function getFacadeAccessor()
	{
		return 'Controller';	
	}

	protected static function addFacadeApp()
	{
		static::$app[static::getFacadeAccessor()] = '\JayaInstitute\Controller';
	}


}