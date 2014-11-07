<?php


class Config extends Facade {

	protected static function getFacadeAccessor()
	{
		return 'Config';	
	}

	protected static function addFacadeApp()
	{
		static::$app[static::getFacadeAccessor()] = '\JayaInstitute\Config';
	}


}