<?php 

namespace JayaInstitute;

class Url {

	protected static $baseUrl;

	public function __construct()
	{
		if ( ! defined('BASEURL') OR BASEURL == '')
		{
			if (isset($_SERVER['HTTP_HOST']))
			{
				// jika ssl aktif kita pake https kalo gak ya http
				$baseUrl = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
				$baseUrl .= '://'. $_SERVER['HTTP_HOST'];
				$baseUrl .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
				static::$baseUrl = $baseUrl;
			}
			else
			{
				// kalo akses dari local set jadi localhost
				static::$baseUrl = 'http://localhost/'; 
			}
		} 
		else
		{
			static::$baseUrl = BASEURL;
		}
			
	}

	public function baseUrl()
	{
		return static::$baseUrl;
	}

}