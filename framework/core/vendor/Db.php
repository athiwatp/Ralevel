<?php

namespace JayaInstitute;

abstract class Db {
	
	protected static $connection;

	protected static $booted = array();

	protected $table;

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

	protected function getColumns($columns)
	{
		return (is_array($columns) && empty($columns)) ? $this->columns : $columns;
	}

	public function find($id, array $columns = array())
	{
		$columns = $this->getColumns($columns);

		$sql = "select * from santris where id = '$id'";
		$this->result = mysqli_query(static::$connection, $sql);
		$row = mysqli_fetch_array($this->result);
		foreach ($row as $key => $value) {
			if (!is_numeric($key)) $this->items[$key] = $value;
			if (is_numeric($key)) unset($row[$key]);
		}

		return $this;
	}

	public function toArray()
	{
		return array_map(function($value)
		{
			return $value instanceof ArrayableInterface ? $value->toArray() : $value;

		}, $this->items);
	}

	public function __toString()
	{
		return json_encode($this->toArray());
	}
	
}

