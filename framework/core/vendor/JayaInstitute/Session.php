<?php

namespace JayaInstitute;

class Session {

	protected static $storage;

	protected static $session;

	protected static $new;

	protected static $old;

	protected static $uid;

	protected static $data;


	public function start($boolean = true)
	{
	 	static::$storage = storage_path('sessions');
	 	
	 	if ($boolean) session_start();
	 	
	 	static::$uid = sha1(session_id());

	 	static::$session = static::$storage.$this->getUID();
	 
	 	$this->readRaw();
	} 


	public function getUID()
	{
		return static::$uid;
	}

	public function readRaw()
	{
		$session = static::$session;
		
		if (! file_exists($session)) return static::$data = array();

		$raw = file_get_contents($session);

		if ($raw == '') return static::$data = array();

		$readRaw = explode(';', $raw);

		$data = array();
	 	
	 	foreach ($readRaw as $item) {
	 		$item = explode('=', $item);
	 		$value = str_replace('<titikkoma>', ';', $item[1]);
	 		$data[$item[0]] = (substr($value, 0, 1) == '[' && substr($value, -1, 1) == ']') ? json_decode(substr($value, 1, strlen($value) -2), true) : $value; 
	 	}

	 	static::$data = $data;

	 	// static::$flash = $this->get('flashVars');


	}

	public function writeRaw()
	{
		$data = static::$data;

		$raw = array();

		foreach ($data as $key => $value) {
			$value = str_replace(';', '<titikkoma>', $value);
			if (is_array($value)) $value = '['.json_encode($value).']';
			$raw[] = implode('=', array($key, $value));
		}

		$raw = implode(';', $raw);

		$session = static::$session;

		file_put_contents($session, $raw);
	}

	public function put($key = '', $value = '')
	{
		if ($key == '' OR $value == '') return $this->writeRaw();

		$data = static::$data;

		$data[$key] = $value;

		static::$data = $data;

		$this->writeRaw();
	}

	public function get($key='', $default = null)
	{
		if (! $this->has($key)) return value($default);

		return static::$data[$key];
	}

	public function push($key = '', $value)
	{
		if ($key == '' OR $value == '') return;

		$val = $this->get($key);

		$val =  (! is_array($val)) ?  ((! empty($val)) ? array($val) : array()) : $val;

		$val[] = $value;

		return $this->put($key, $val);
	}

	public function all()
	{
		return static::$data;
	}

	public function has($key = '')
	{
		if ($key == '') return false;

		$data = static::$data;

		if (! array_key_exists($key, $data)) return false;

		return true;
	}

	public function forget($key)
	{
		if (!$this->has($key)) return;

		$data = static::$data;

		unset($data[$key]);

		static::$data = $data;

		return $this->put();
	}

	public function flush()
	{
		static::$data = array();

		return $this->put();
	}

	public function regenerate()
	{
		$data = $this->all();

		$this->destroy(static::$session);

		session_regenerate_id(true);

		$this->start(false);

		static::$data = $data;

		return $this->put();
	}

	public function destroy($session)
	{
		if (file_exists(static::$session)) unlink(static::$session);
	}

	// public function flash($key = '', $value='')
	// {
	// 	return $this->push('newFlashVars', array($key => $value));
	// }

	// public function reflash()
	// {
	// 	foreach (static::$flash as $key => $val) {
	// 		$this->flash($key, $value);
	// 	}
	// }

	// public function keep($keys='')
	// {
	// 	if (!is_array($keys)) $keys = array($keys);

	// 	foreach ($keys as $key) {
	// 		$this->flash($key, $this->get($key));
	// 	}
	// }
}