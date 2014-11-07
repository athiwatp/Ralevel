<?php


class Route extends Facade {

	protected static function getFacadeAccessor()
	{
		return 'Route';	
	}

	protected static function addFacadeApp()
	{
		static::$app[static::getFacadeAccessor()] = '\JayaInstitute\Route';
	}


}