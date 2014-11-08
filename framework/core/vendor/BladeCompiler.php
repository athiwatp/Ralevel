<?php namespace Illuminate\View\Compilers;

class BladeCompiler {

	protected $sectionStack = array();

	protected $sections = array();


	/**
	 * All of the registered extensions.
	 *
	 * @var array
	 */
	protected $extensions = array();

	/**
	 * The file currently being compiled.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * All of the available compiler functions.
	 *
	 * @var array
	 */
	protected $compilers = array(
		'Extensions',
		'Extends',
		'Comments',
		'Echos',
		'Openings',
		'Closings',
		'Else',
		'Unless',
		'EndUnless',
		'Includes',
		'Each',
		'Yields',
		'Shows',
		'SectionStart',
		'SectionStop',
		'SectionAppend',
		'SectionOverwrite',
	);

	/**
	 * Array of opening and closing tags for echos.
	 *
	 * @var array
	 */
	protected $contentTags = array('{{', '}}');

	/**
	 * Array of opening and closing tags for escaped echos.
	 *
	 * @var array
	 */
	protected $escapedTags = array('{{{', '}}}');

	/**
	 * Compile the view at the given path.
	 *
	 * @param  string  $path
	 * @return void
	 */
	public function compile($path = null)
	{
		if ($path)
		{
			$this->setPath($path);
		}

		$contents = $this->compileString(file_get_contents($this->getPath()));

		return $contents;

	}

	public function make($path = '', array $data = array())
	{
		if (isset($data['data'])) extract($data['data']);
		$__env = $data['__env'];
		unset($data);
		$compiled = $__env->compile($path);
		return eval( '?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', $compiled)) );

	}

	public function startSection($section, $content = '')
	{
		if ($content === '')
		{
			ob_start() && $this->sectionStack[] = $section;
		}
		else
		{
			$this->extendSection($section, $content);
		}
	}

	public function stopSection($overwrite = false)
	{
		$last = array_pop($this->sectionStack);

		if ($overwrite)
		{
			$this->sections[$last] = ob_get_clean();
		}
		else
		{
			$this->extendSection($last, ob_get_clean());
		}

		return $last;
	}

	public function yieldContent($section, $default = '')
	{
		return isset($this->sections[$section]) ? $this->sections[$section] : $default;
	}

	protected function extendSection($section, $content)
	{
		if (isset($this->sections[$section]))
		{
			$content = str_replace('@parent', $content, $this->sections[$section]);

			$this->sections[$section] = $content;
		}
		else
		{
			$this->sections[$section] = $content;
		}
	}

	/**
	 * Get the path currently being compiled.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Set the path currently being compiled.
	 *
	 * @param string $path
	 * @return void
	 */
	public function setPath($path)
	{
		$path = APPPATH.'views/'.str_replace('.', '/', $path).'.php';

		$this->path = $path;
	}

	/**
	 * Compile the given Blade template contents.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public function compileString($value)
	{
		foreach ($this->compilers as $compiler)
		{
			$value = $this->{"compile{$compiler}"}($value);
		}

		return $value;
	}

	/**
	 * Register a custom Blade compiler.
	 *
	 * @param  Closure  $compiler
	 * @return void
	 */
	public function extend($compiler)
	{
		$this->extensions[] = $compiler;
	}

	/**
	 * Execute the user defined extensions.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileExtensions($value)
	{
		foreach ($this->extensions as $compiler)
		{
			$value = call_user_func($compiler, $value, $this);
		}

		return $value;
	}

	/**
	 * Compile Blade template extensions into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileExtends($value)
	{
		// By convention, Blade views using template inheritance must begin with the
		// @extends expression, otherwise they will not be compiled with template
		// inheritance. So, if they do not start with that we will just return.
		if (strpos($value, '@extends') !== 0)
		{
			return $value;
		}

		$lines = preg_split("/(\r?\n)/", $value);


		// Next, we just want to split the values by lines, and create an expression
		// to include the parent layout at the end of the templates. Which allows
		// the sections to get registered before the parent view gets rendered.
		$lines = $this->compileLayoutExtends($lines);

		return implode("\r\n", array_slice($lines, 1));
	}

	/**
	 * Compile the proper template inheritance for the lines.
	 *
	 * @param  array  $lines
	 * @return array
	 */
	protected function compileLayoutExtends($lines)
	{
		$pattern = $this->createMatcher('extends');

		$lines[] = preg_replace($pattern, '$1@include$2', $lines[0]);

		return $lines;
	}

	/**
	 * Compile Blade comments into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileComments($value)
	{
		$pattern = sprintf('/%s--((.|\s)*?)--%s/', $this->contentTags[0], $this->contentTags[1]);

		return preg_replace($pattern, '<?php /*$1*/ ?>', $value);
	}

	/**
	 * Compile Blade echos into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileEchos($value)
	{
		$difference = strlen($this->contentTags[0]) - strlen($this->escapedTags[0]);

		if ($difference > 0)
		{
			return $this->compileEscapedEchos($this->compileRegularEchos($value));
		}

		return $this->compileRegularEchos($this->compileEscapedEchos($value));
	}

	/**
	 * Compile the "regular" echo statements.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileRegularEchos($value)
	{
		$me = $this;

		$pattern = sprintf('/(@)?%s\s*(.+?)\s*%s/s', $this->contentTags[0], $this->contentTags[1]);

		$callback = function($matches) use ($me)
		{
			return $matches[1] ? substr($matches[0], 1) : '<?php echo '.$me->compileEchoDefaults($matches[2]).'; ?>';
		};

		return preg_replace_callback($pattern, $callback, $value);
	}

	/**
	 * Compile the escaped echo statements.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileEscapedEchos($value)
	{
		$me = $this;

		$pattern = sprintf('/%s\s*(.+?)\s*%s/s', $this->escapedTags[0], $this->escapedTags[1]);

		$callback = function($matches) use ($me)
		{
			return '<?php echo e('.$me->compileEchoDefaults($matches[1]).'); ?>';
		};

		return preg_replace_callback($pattern, $callback, $value);
	}

	/**
	 * Compile the default values for the echo statement.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public function compileEchoDefaults($value)
	{
		return preg_replace('/^(?=\$)(.+?)(?:\s+or\s+)(.+?)$/s', 'isset($1) ? $1 : $2', $value);
	}

	/**
	 * Compile Blade structure openings into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileOpenings($value)
	{
		$pattern = '/(?(R)\((?:[^\(\)]|(?R))*\)|(?<!\w)(\s*)@(if|elseif|foreach|for|while)(\s*(?R)+))/';

		return preg_replace($pattern, '$1<?php $2$3: ?>', $value);
	}

	/**
	 * Compile Blade structure closings into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileClosings($value)
	{
		$pattern = '/(\s*)@(endif|endforeach|endfor|endwhile)(\s*)/';

		return preg_replace($pattern, '$1<?php $2; ?>$3', $value);
	}

	/**
	 * Compile Blade else statements into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileElse($value)
	{
		$pattern = $this->createPlainMatcher('else');

		return preg_replace($pattern, '$1<?php else: ?>$2', $value);
	}

	/**
	 * Compile Blade unless statements into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileUnless($value)
	{
		$pattern = $this->createMatcher('unless');

		return preg_replace($pattern, '$1<?php if ( !$2): ?>', $value);
	}

	/**
	 * Compile Blade end unless statements into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileEndUnless($value)
	{
		$pattern = $this->createPlainMatcher('endunless');

		return preg_replace($pattern, '$1<?php endif; ?>$2', $value);
	}

	/**
	 * Compile Blade include statements into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileIncludes($value)
	{
		$pattern = $this->createOpenMatcher('include');

		$replace = '$1<?php echo $__env->make$2, get_defined_vars()); ?>';

		return preg_replace($pattern, $replace, $value);
	}

	/**
	 * Compile Blade each statements into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileEach($value)
	{
		$pattern = $this->createMatcher('each');

		return preg_replace($pattern, '$1<?php echo $__env->renderEach$2; ?>', $value);
	}

	/**
	 * Compile Blade yield statements into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileYields($value)
	{
		$pattern = $this->createMatcher('yield');

		return preg_replace($pattern, '$1<?php echo $__env->yieldContent$2; ?>', $value);
	}

	/**
	 * Compile Blade show statements into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileShows($value)
	{
		$pattern = $this->createPlainMatcher('show');

		return preg_replace($pattern, '$1<?php echo $__env->yieldSection(); ?>$2', $value);
	}

	/**
	 * Compile Blade section start statements into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileSectionStart($value)
	{
		$pattern = $this->createMatcher('section');

		return preg_replace($pattern, '$1<?php $__env->startSection$2; ?>', $value);
	}

	/**
	 * Compile Blade section stop statements into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileSectionStop($value)
	{
		$pattern = $this->createPlainMatcher('stop');

		$value = preg_replace($pattern, '$1<?php $__env->stopSection(); ?>$2', $value);

		$pattern = $this->createPlainMatcher('endsection');

		return preg_replace($pattern, '$1<?php $__env->stopSection(); ?>$2', $value);
	}

	/**
	 * Compile Blade section append statements into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileSectionAppend($value)
	{
		$pattern = $this->createPlainMatcher('append');

		return preg_replace($pattern, '$1<?php $__env->appendSection(); ?>$2', $value);
	}

	/**
	 * Compile Blade section stop statements into valid PHP.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function compileSectionOverwrite($value)
	{
		$pattern = $this->createPlainMatcher('overwrite');

		return preg_replace($pattern, '$1<?php $__env->stopSection(true); ?>$2', $value);
	}

	/**
	 * Get the regular expression for a generic Blade function.
	 *
	 * @param  string  $function
	 * @return string
	 */
	public function createMatcher($function)
	{
		return '/(?<!\w)(\s*)@'.$function.'(\s*\(.*\))/';
	}

	/**
	 * Get the regular expression for a generic Blade function.
	 *
	 * @param  string  $function
	 * @return string
	 */
	public function createOpenMatcher($function)
	{
		return '/(?<!\w)(\s*)@'.$function.'(\s*\(.*)\)/';
	}

	/**
	 * Create a plain Blade matcher.
	 *
	 * @param  string  $function
	 * @return string
	 */
	public function createPlainMatcher($function)
	{
		return '/(?<!\w)(\s*)@'.$function.'(\s*)/';
	}

	/**
	 * Sets the content tags used for the compiler.
	 *
	 * @param  string  $openTag
	 * @param  string  $closeTag
	 * @param  bool    $escaped
	 * @return void
	 */
	public function setContentTags($openTag, $closeTag, $escaped = false)
	{
		$property = ($escaped === true) ? 'escapedTags' : 'contentTags';

		$this->{$property} = array(preg_quote($openTag), preg_quote($closeTag));
	}

	/**
	 * Sets the escaped content tags used for the compiler.
	 *
	 * @param  string  $openTag
	 * @param  string  $closeTag
	 * @return void
	 */
	public function setEscapedContentTags($openTag, $closeTag)
	{
		$this->setContentTags($openTag, $closeTag, true);
	}

	/**
	* Gets the content tags used for the compiler.
	*
	* @return string
	*/
	public function getContentTags()
	{
		return $this->contentTags;
	}

	/**
	* Gets the escaped content tags used for the compiler.
	*
	* @return string
	*/
	public function getEscapedContentTags()
	{
		return $this->escapedTags;
	}

}
