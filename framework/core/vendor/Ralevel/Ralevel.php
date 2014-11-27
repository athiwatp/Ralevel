<?php 

class Ralevel {

	protected static $calledClass = array();
	
	protected static $calledController = array();

	public function init()
	{
		$directory = APPPATH.'controllers/';
		
		$dir = @dir($directory);
		while (false !== ($entry = $dir->read()) ) 
		{
			if ( '.' == $entry || '..' == $entry ) continue;

			if (array_key_exists($entry, static::$calledClass)) continue; 

			$code = file_get_contents($directory.$entry);

			$code = str_replace('<?php', '', $code);

			$code = str_replace('function', 'static function', $code);
			
			$code = str_replace('$this->', 'static::', $code);

			eval($code);

			static::$calledClass[$entry] = 'loaded';
		}
	}

	public function run()
	{
		$this->init();

		Session::start();

		$starter = Route::start();
	
		if (is_callable($starter)) return $starter();

		if (is_string($starter) OR is_numeric($starter) OR is_array($starter) OR is_bool($starter)) echo json_encode($starter);

		if (is_stringify($starter)) echo $starter->toString();

		
		return;


	}

} 