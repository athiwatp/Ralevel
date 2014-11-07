<?php


class View extends Facade {

	protected static function getFacadeAccessor()
	{
		return 'View';	
	}

	protected static function addFacadeApp()
	{
		static::$app[static::getFacadeAccessor()] = '\JayaInstitute\View';
	}


}