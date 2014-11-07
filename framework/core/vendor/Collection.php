<?php

namespace JayaInstitute; 

class Collection {

	protected $items = array();

	public function __construct(array $items = array())
	{
		$this->items = $items;
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