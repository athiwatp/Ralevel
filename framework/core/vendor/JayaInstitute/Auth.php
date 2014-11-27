<?php

namespace JayaInstitute;
use Ralevel\Model\User;

class Auth {

	protected static $logged = false;

	protected static $remember = false;

	protected static $user;


	public function __construct()
	{
		$tokenBySession = \Session::getUID();

		if (\Session::get('logged') == true)
		{
			static::$logged = true; 

			static::$user = \User::find(\Session::get('auth_logged_user_id'))->toObject();
		} 

		if (\User::where('token_remember', '=', $tokenBySession)->get()->toArray() != array()) $remember = true;
	}

	public function login(User $user, $remember = false, $withSession = true)
	{
		if ( $user->toArray() == array() )	return false;
		
		static::$remember = true;

		static::$logged = true;

		static::$user = $user->toObject();

		\Session::regenerate();

		if ($remember) $user->update(array('token_remember' => \Session::getUID()));
		else $user->update(array('last_login' => date('Y-m-d h:m:s')));

		if ($withSession) \Session::put('logged', true);

		\Session::put('auth_logged_user_id', $user->id); 

		// print_r($user->toArray());
		// return;

		// cleaning the old session

		$dir = @dir(storage_path('sessions'));

		while (false !== ($entry = $dir->read()) ) 
		{
			if ( '.' == $entry || '..' == $entry ) continue;

			$diff = time()-filemtime($file = storage_path('sessions').$entry);

			if ($diff > (60 * 60 * 24 * 30)) \Session::destroy($file);
		}		 		
	}

	public function validate($credential)
	{
		$user = \User::where($credential)->get();
		
		return ( $user->toArray() != array() ) ? true : false; 
	}

	public function attempt($credential, $remember = false, $withSession = true)
	{
		$user = \User::where($credential)->get();


		$this->login($user, $remember, $withSession);

		print_r($this->check());
		return $this->check();
	}

	public function viaRemember()
	{
		return (static::$remember == true && static::$logged == true) ? true : false;
	}

	public function check()
	{
		return static::$logged;
	}

	public function user()
	{
		return static::$user;
	}

	public function loginUsingId($id)
	{
		return $this->login(\User::find($id));
	}

	public function once($credential)
	{
		return $this->attempt($credential, false, false);
	}

	public function guest()
	{
		return (! $this->check());
	}

	public function logout()
	{
		if (!is_null(static::$user)) \User::find(static::$user->id)->update(array('token_remember' => ''));

		static::$logged = false;
		static::$remember = false;
		static::$user = null;

		\Session::forget('auth_logged_user_id');
		\Session::forget('logged');
	}
}