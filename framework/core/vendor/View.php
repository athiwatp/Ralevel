<?php

namespace JayaInstitute;

class View extends \Illuminate\View\Compilers\BladeCompiler {

	public function make($template, array $data = array(), $returnValue = FALSE)
	{
		$path = 'framework/app/views/'.str_replace('.', '/', $template).'.php';

		$compiled = $this->compile($path);

		$__env = new View();

		echo $compiled;

		ob_start();
		echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', $compiled)));
		$result = ob_get_contents();
		@ob_end_clean();
		
		if ( $returnValue )
		{
			return $result;
		}
		
		// echo $result;
		return;

	}

}

