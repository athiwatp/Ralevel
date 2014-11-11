<?php

namespace JayaInstitute;

abstract class Db {
	
	protected $connection;

	protected static $booted = array();

	protected $readyAction = 'insert';

	protected $row = 1;

	protected $sql;

	protected $table;

	protected $primaryKey = 'id';

	protected $columns;

	protected $distinct = false;

	protected $from;

	protected $wheres = array();

	protected $group;

	protected $havings = array();

	protected $order;

	protected $offset;

	protected $limit;

	protected $items = array();

	protected $numRows = 0;
	
	public function __construct()
	{
		extract($config = \Config::get('database'));

		$this->connection = mysqli_connect($host, $username, $password, $database, $port);
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

	protected function escapeQuote($value)
	{
		$value = mysqli_escape_string($this->connection, $value);
		return (is_numeric($value)) ? $value : '\''.$value.'\'';
	}

	public function where($column, $operator = null, $value = null, $boolean = 'and', $var = 'wheres')
	{
		if ($operator == null) 
		{
			$this->{$var}[] = strtoupper($boolean).' '.$column;
	
			return $this;
		}
		
		$operator = strtoupper(trim($operator));
		
		if (is_array($value)) 
		{
			if (empty($value)) return $this;

			return $this->where($column, $operator, implode(', ', $value), $boolean, $var);

		}
		elseif ($operator == 'BETWEEN' OR $operator == 'NOT BETWEEN')
		{
			$value = implode(' AND ', array_slice(array_map(function($item){ return $this->escapeQuote($item);}, explode(',', $value)), 0, 2));
		}
		elseif ($operator == 'IN' OR $operator == 'NOT IN')
		{
			$value = '('.implode(', ', array_map(function($item){ return $this->escapeQuote($item);}, explode(',', $value))).')';
		}
		elseif ($operator == 'IS NULL' OR $operator == 'IS NOT NULL')
		{
			$value = '';
		}
		elseif (count(explode(',', $value)) > 0) 
		{
			$value = explode(',', $value);

			$this->{$var}[] = $this->whereClause($column, $operator,  $this->escapeQuote(array_shift($value)), $boolean);

			return $this->where($column, $operator, $value, 'or', $var);
		}
		else 
		{
			$value = $this->escapeQuote($value);
		}

		$this->{$var}[] = $this->whereClause($column, $operator,  $value, $boolean);

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

	public function having($column, $operator = null, $value = null, $boolean = 'and')
	{
		return $this->where($column, $operator, $value, $boolean, 'havings');
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

		$havings = $this->havings;
		
		$sql  = 'SELECT '.($this->distinct ? 'DISTINCT ': '').$columns.' FROM '.$this->getTable();
		
		if (! empty($wheres))
			$sql .= ' WHERE '.ltrim(array_shift($wheres), 'OR AND').' '.implode(' ', $wheres);
		
		if (! empty($this->group))
			$sql .= ' GROUP BY '.$this->group;

		if (! empty($havings))
			$sql .= ' HAVING '.ltrim(array_shift($havings), 'OR AND').' '.implode(' ', $havings);

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
		$this->row = 1;

	 	$this->distinct = false;

	 	$this->wheres = array();

	 	$this->group = '';

	 	$this->havings = array();

	 	$this->order = '';

	 	$this->offset = '';

	 	$this->limit = '';

	 	$this->items = array();

	 	$this->numRows = 0;
	}

	public function get($columns = array('*'))
	{
		$this->sql = $sql = ($this->buildSelect($columns));

		$this->reset();
		
		$this->readyAction = 'update/delete';

		if (!$query = mysqli_query($this->connection, $sql)) return $this;
		
		$this->numRows = $query->num_rows;

		while ($row = mysqli_fetch_array($query)) 
		{
			foreach ($row as $key => $value) {
				if (is_numeric($key)) unset($row[$key]);
			}

			$this->items[] = $row;
		}

		return $this;
	}

	public function numRows()
	{
		return $this->numRows;
	}

	public function create($items = array(), $returnSql = false)
	{
		$this->readyAction = 'insert';

		$this->items = $items;
		
		if ($items == array()) return $this;
		
		if ($returnSql) return $this->saveSql();
		
		return $this->save();
	}

	public function update($items = array(), $returnSql = false)
	{
		if ($items == array()) return $this;
		
		$this->readyAction = 'update';

		$oldItems = $this->items;

		if (empty($oldItems)) return $this;

		if (!array_key_exists(0, $oldItems))
		{
			$this->items = array_merge($oldItems, $items);
		}
		else
		{
			array_walk($oldItems, function(&$value, $key, $items){ $value = array_merge($value, $items); }, $items);
			$this->items = $oldItems;
		}
		
		if ($returnSql) return $this->saveSql();
	
		return $this->save();
	}

	public function save($boolean = true)
	{
		$this->saveSql(false);
		
		return $this;
	}

	public function delete()
	{
		$this->deleteSql(false);

		return $this;
	}

	public function deleteSql($toSql = true)
	{
		if ($this->readyAction != 'update/delete') return $this;
	
		$sql = '';		

		$deleteKey = $this->itemsToDelete();

		if (empty($deleteKey)) return $this->$sql = '';
		
		foreach ($deleteKey as $key) {
			
			$sql .= $sqlSyntax = 'DELETE FROM '.$this->getTable().' WHERE '.$this->primaryKey.' = '.$this->escapeQuote($key).'; ';

			if (!$toSql)
			{
				$query = mysqli_query($this->connection, $sqlSyntax);
			}
		}

		$this->find($deleteKey);

		return $this->sql = $sql;
	}

	public function saveSql($toSql = true)
	{
		$sql = '';		

		$items = $this->itemsToSave();

		if (empty($items)) return $this->$sql = '';
		
		$inserted_id = array();

		foreach ($items as $item) {
	
			$sql .= $sqlSyntax = ($this->readyAction == 'insert') ? $this->buildInsert($item) : $this->buildUpdate($item);
	
			if (!$toSql) 
			{
				$query = mysqli_query($this->connection, $sqlSyntax);
	
				$inserted_id[] = ($this->readyAction == 'insert') ? mysqli_insert_id($this->connection) : $item[$this->primaryKey];
			}
	
		}

		if ( ! empty($inserted_id))  $this->find($inserted_id);
			
		return $this->sql = $sql;
	}

	protected function itemsToSave()
	{
		$items = $this->items;

		if (empty($items)) return array();
		
		if (!array_key_exists(0, $items)) $items = array($items);

		$newItems = array();

		foreach ($items as $item) {

			if (empty($item)) continue;

			if (!isset($item[$this->primaryKey]) && $this->readyAction == 'update/delete') continue;

			if (isset($this->fillable) && is_array($this->fillable) && ! empty($this->fillable)) 
				$item = array_intersect_key($item, $this->fillable);
	
			if (isset($this->guarded) && is_array($this->guarded) && ! empty($this->guarded)) 
				$item = array_diff_key($item, $this->guarded);

			$newItems[] = $item;
		
		}
		
		return $newItems;
	}

	protected function itemsToDelete()
	{
		$items = $this->items;

		if (empty($items)) return array();
		
		if (!array_key_exists(0, $items)) $items = array($items);

		$deleteKey = array();

		foreach ($items as $item) {

			if (empty($item)) continue;

			if (!isset($item[$this->primaryKey]) && $this->readyAction == 'update/delete') continue;

			$deleteKey[] = $item[$this->primaryKey];
		
		}
		
		return $deleteKey;
	}

	protected function buildInsert($item)
	{
		$columns = array_keys($item);
	
		$values = array_values($item);

		return $sql = 'INSERT INTO '.$this->getTable().' ('.implode(', ', $columns).') VALUES ('.implode(', ', array_map(function(&$v){ return $this->escapeQuote($v);}, $values)).'); ';
	}

	public function buildUpdate($items)
	{
		$item = $items;

		$pKey = $this->primaryKey;

		$pValue = $items[$pKey];

		$item = array_diff_key($item, array($pKey => ''));
		
		array_walk($item, function(&$value, $key){ $value = $this->escapeQuote($value); $value = $key.' = '.$value;});
		
		$values = array_values($item);

		return $sql = 'UPDATE '.$this->getTable().' SET '.implode(', ', $values).' WHERE '.$pKey.' = '.$this->escapeQuote($pValue).'; ';
	}

	public function row($value = 1, $items = array())
	{
		$this->row = min(count($this->items) + 1, $value);

		foreach ($items as $key => $value) {
			$this->$key = $value;
		}

		return $this;
	}

	public function __get($key)
	{
		if (empty($this->items)) return '';

		$row = $this->row - 1;
		
		if (array_key_exists($key, $this->items[$row])) return $this->items[$row][$key];
	
		return '';
	}

	public function __set($key, $value = '')
	{
		if ($key == '') return;

		$row = ($this->row) - 1;

		if (count($this->items) == $row) $this->items[] = array();

		if (!isset($this->items[$row])) return;

		$this->items[$row][$key] = $value;
	
		return;
	}

	public function getTable()
	{
		if (isset($this->table) && ! empty($this->table)) return $this->table;

		return strtolower(substr($class = str_replace('\\', '', class_basename($this)), 0, strlen($class) - 5));
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
	
	public function tesEscape()
	{
		$tes = "select * from tes where nama = 'bejo'";
		var_dump(mysqli_escape_string($this->connection, $tes));
	}

}

