<?php

namespace JayaInstitute;

use \Illuminate\View\Compilers\BladeCompiler;

class View {

	protected $result;

	public function make($template = '', array $data = array(), $returnValue = FALSE)
	{
		
		$__env = new BladeCompiler();

		$compiled = $__env->compile($template);

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

