<?php


class Uri extends Facade {

	protected static function getFacadeAccessor()
	{
		return 'Uri';	
	}

	protected static function addFacadeApp()
	{
		static::$app[static::getFacadeAccessor()] = '\JayaInstitute\Uri';
	}


}