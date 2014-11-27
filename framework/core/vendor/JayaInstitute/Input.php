<?php

namespace JayaInstitute;

class Input {

	public function get($key = null, $default = null)
	{
		if ($key == null) return array_map(function($v){return $this->escapeInput($v);}, $_REQUEST);

		if (array_key_exists($key, $_REQUEST)) return $this->escapeInput($_REQUEST[$key]);

		return value($default);
	}

	public function escapeInput($value)
	{
		if (is_array($value))
		{
			foreach ($value as $key => $val) {
				$value[$key] = $this->escapeInput($val);
			}

			return $value;
		}
		
		return stripslashes(strip_tags(htmlspecialchars($value,ENT_QUOTES)));
	} 

	public function all()
	{
		return $this->get();
	}

	public function only($args)
	{
		return array_intersect_key($this->get(), array_flip(func_get_args()));
	}

	public function except($args)
	{
		return array_diff_key($this->get(), array_flip(func_get_args()));
	}

}