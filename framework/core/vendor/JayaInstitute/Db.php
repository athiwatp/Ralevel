<?php

namespace JayaInstitute;

abstract class Db {
	
	protected static $connection;

	protected static $booted = array();

	protected $table;

	protected $primaryKey = 'id';

	protected $columns = '*';

	protected $items = array();
	
	public function __construct(array $items = array())
	{
		$this->items = $items;

		extract($config = \Config::get('database'));

		static::$connection = mysqli_connect($host, $username, $password, $database, $port);
	}

	public function getTable()
	{
		if (isset($this->table)) return $this->table;

		return str_replace('\\', '', class_basename($this));
	}

	public function getColumns(array $columns = array())
	{
		return (is_array($columns) && empty($columns)) ? $this->columns : $columns;
	}

	public function find($key, array $columns = array())
	{
		$columns = $this->getColumns($columns);
		return $this->select($columns)->where($this->primaryKey, '=', $key)->first();
	}

	public function toArray()
	{
		return $this->items;
	}

	public function __toString()
	{
		return json_encode($this->toArray());
	}
	
}

