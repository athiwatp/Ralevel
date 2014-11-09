<?php

namespace JayaInstitute;

abstract class Db {
	
	protected static $connection;

	protected static $booted = array();

	protected $readyAction;

	protected $sql;

	protected $table;

	protected $primaryKey = 'id';

	protected $columns;

	protected $distinct = false;

	protected $from;

	protected $wheres = array();

	protected $group;

	protected $having;

	protected $order;

	protected $offset;

	protected $limit;

	protected $items = array();

	protected $numRows;
	
	public function __construct()
	{
		extract($config = \Config::get('database'));

		static::$connection = mysqli_connect($host, $username, $password, $database, $port);
	}

	public function select($columns = array('*'))
	{
		$this->columns = is_array($columns) ? $columns : func_get_args();

		return $this;
	}

	public function distinct()
	{
		$this->distinct = true;

		return $this;
	}

	public function addSelect($column)
	{
		$column = is_array($column) ? $column : func_get_args();

		$this->columns = array_merge((array) $this->columns, $column);

		return $this;
	}

	public function from($table)
	{
		$this->from = $table;

		return $this;
	}

	public function where($column, $operator = null, $value = null, $boolean = 'and')
	{
		if ($operator == null) 
		{
			$this->wheres[] = strtoupper($boolean).' '.$column;
			return $this;
		}
		
		$operator = strtoupper(trim($operator));
		
		if ($operator == 'BETWEEN' OR $operator == 'NOT BETWEEN')
		{
			$value = implode(' AND ', array_slice(array_map(function($item){ $newItem = str_replace('\'', '\'\'', trim($item)); return (is_numeric($newItem)) ? $newItem : '\''.$newItem.'\'';}, explode(',', $value)), 0, 2));
		}
		elseif ($operator == 'IN' OR $operator == 'NOT IN')
		{
			$value = '('.implode(' ', array_slice(array_map(function($item){ $newItem = str_replace('\'', '\'\'', trim($item)); return (is_numeric($newItem)) ? $newItem : '\''.$newItem.'\'';}, explode(',', $value)), 0, 2)).')';
		}
		elseif ($operator == 'IS NULL' OR $operator == 'IS NOT NULL')
		{
			$value = '';
		}
		elseif (is_array($value)) 
		{
			if (empty($value)) return $this;

			$this->wheres[] = $this->whereClause($column, $operator,  array_shift($value), $boolean);

			return $this->orWhere($column, $operator, $value);
		}
		elseif (count($values = explode(',', $value)) > 0) 
		{
			return $this->where($column, $operator, $values);
		}
		else 
		{
			$value = trim($value);
			$value = (is_numeric($value) ? $value : '\''.$value.'\'');
		}

		$this->wheres[] = $this->whereClause($column, $operator,  $value, $boolean);

		return $this;

	}

	protected function whereClause($column, $operator,  $value, $boolean)
	{
		return strtoupper($boolean).' '.'`'.$column.'` '.$operator.' '.$value;
	}

	public function orWhere($column, $operator = null, $value = null)
	{
		return $this->where($column, $operator, $value, 'or');
	}

	public function groupBy($columns)
	{
		$this->group = $columns;
		return $this;
	}

	public function having($column, $operator = null, $value = null)
	{
		if ($operator == null) 
		{
			$this->having = $column;
			return $this;
		}
		
		$operator = strtoupper(trim($operator));
		
		if ($operator == 'BETWEEN' OR $operator == 'NOT BETWEEN')
		{
			$value = implode(' AND ', array_slice(array_map(function($item){ $newItem = str_replace('\'', '\'\'', trim($item)); return (is_numeric($newItem)) ? $newItem : '\''.$newItem.'\'';}, explode(',', $value)), 0, 2));
		}

		if ($operator == 'IN' OR $operator == 'NOT IN')
		{
			$value = '('.implode(' ', array_slice(array_map(function($item){ $newItem = str_replace('\'', '\'\'', trim($item)); return (is_numeric($newItem)) ? $newItem : '\''.$newItem.'\'';}, explode(',', $value)), 0, 2)).')';
		}

		if ($operator == 'IS NULL' OR $operator == 'IS NOT NULL')
		{
			$value = '';
		}

		$clause = $column.' '.$operator.' '.$value;

		$this->having = $clause;

		return $this;
	}	

	public function orderBy($column, $direction = 'asc')
	{
		$this->order = $column.(strtoupper($direction) == 'ASC' ? ' ASC' : ' DESC');
		return $this;
	}

	public function latest($column = 'created_at')
	{
		return $this->orderBy($column, 'desc');
	}

	public function oldest($column = 'created_at')
	{
		return $this->orderBy($column, 'asc');
	}

	public function offset($value)
	{
		$this->offset = max(0, $value);
		return $this;
	}

	public function skip($value)
	{
		return $this->offset($value);
	}

	public function limit($value)
	{
		if ($value > 0) $this->limit = $value;
		return $this;
	}

	public function take($value)
	{
		return $this->limit($value);
	}

	public function forPage($page, $perPage = 15)
	{
		return $this->skip(($page - 1) * $perPage)->take($perPage);
	}

	public function sql($value='')
	{
		$this->sql = $value;
		return $this;
	}

	public function toSql()
	{
		return $this->sql;
	}

	public function find($id, $columns = array('*'))
	{
		return $this->distinct()->where($this->primaryKey, '=', $id)->get($columns);
	}

	public function first($columns = array('*'))
	{
		$results = $this->take(1)->get($columns)->toArray();

		$this->items = count($results) > 0 ? reset($results) : null;
		return $this;
	}

	protected function buildSelect($columns = array('*'))
	{
		if (empty($this->columns) OR $columns != array('*')) $this->select($columns); 
		$columns = ! is_array($this->columns) ? $this->columns : implode(', ', $this->columns);
		$wheres = $this->wheres;

		
		$sql  = 'SELECT '.($this->distinct ? 'DISTINCT ': '').$columns.' FROM '.$this->getTable();
		
		if (! empty($wheres))
			$sql .= ' WHERE '.ltrim(array_shift($wheres), 'OR AND').' '.implode(' ', $wheres);
		
		if (! empty($this->group))
			$sql .= ' GROUP BY '.$this->group;

		if (! empty($this->having))
			$sql .= ' HAVING '.$this->having;

		if (! empty($this->order))
			$sql .= ' ORDER BY '.$this->order;

		if (! empty($this->limit))
			$sql .= ' LIMIT '.$this->limit;

		if (! empty($this->offset))
			$sql .= ' OFFSET '.$this->offset;

		return $sql;
	}

	protected function reset()
	{
	 	$this->distinct = false;

	 	$this->wheres = array();

	 	$this->group = '';

	 	$this->having = '';

	 	$this->order = '';

	 	$this->offset = '';

	 	$this->limit = '';

	 	$this->items = array();
	}

	public function get($columns = array('*'))
	{
		$this->sql = $sql = ($this->buildSelect($columns));

		$this->reset();
		$query = mysqli_query(static::$connection, $sql);
		$this->numRows = $query->num_rows;

		while ($row = mysqli_fetch_array($query)) 
		{
			foreach ($row as $key => $value) {
				if (is_numeric($key)) unset($row[$key]);
			}

			$this->items[] = $row;
		}

		if (! empty($this->items) ) $this->readyAction = 'update/delete';
		
		return $this;
	}

	public function create($items = array())
	{
		if ($items == array()) return;
		$this->readyAction = 'insert';
		$this->items = $items;
		$this->save();
		return $this;
	}

	public function update($items = array())
	{
		if ($items == array()) return;
		$this->readyAction = 'update';
		$this->items = (!array_key_exists(0, $this->items)) ? array_merge($this->items, $items) : array_map(function($v){return array_merge($v, $items); }, $this->items);
		$this->save();
		return $this;
	}

	public function delete()
	{
		
	}

	public function saveUrl()
	{
		$items = $this->items;

		if (empty($items)) return '';
		
		if (isset($this->fillable) && is_array($this->fillable) && ! empty($this->fillable)) 
			$items = array_intersect_key($items, $this->fillable);

		if (isset($this->guarded) && is_array($this->guarded) && ! empty($this->guarded)) 
			$items = array_diff_key($items, $this->guarded);

		return $this->$sql = ($this->readyAction == 'insert') ? $this->buildInsert() : $this->buildUpdate();
	}

	protected function buildInsert()
	{

	}

	public function __get($key)
	{
		if (array_key_exists($key, $this->items)) return $this->items[$key];
		return '';
	}

	public function __set($key, $value = '')
	{
		$this->items[$key] = $value;
		return;
	}

	public function getTable()
	{
		if (isset($this->table) && ! empty($this->table)) return $this->table;

		return str_replace('\\', '', class_basename($this));
	}

	public function toArray()
	{
		return $this->items;
		
	}

	public function toString()
	{
		return json_encode($this->toArray());
	}

	public function __toString()
	{
		return $this->toString();
	}
	
}

