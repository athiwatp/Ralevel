<?php

namespace JayaInstitute;

use \Illuminate\View\Compilers\BladeCompiler;

class View {

	protected $result;

	public function make($template = '', array $data = array(), $returnValue = FALSE)
	{
		
		$__env = new BladeCompiler();

		$_data = array_merge(array('data' => $data), array('__env'  => $__env));

		$compiled = $__env->make($template, $_data);

		ob_start();
		echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', $compiled)));
		$this->result = ob_get_contents();
		@ob_end_clean();
		
		if (! $returnValue) echo $this->result;
		return $this;
	}

	public function __toString()
	{
		return $this->result;
	}

}

