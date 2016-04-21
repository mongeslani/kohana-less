<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Less {

	// Default less files extension
	public static $ext = '.less';

	/**
	 * Get the link tag of less paths
	 *
	 * @param   mixed     array of css paths or single path
	 * @param   string    value of media css type
	 */
	public static function compile($files = '', $media = 'screen')
	{
		if (Kohana::$profiling) {
			$benchmark = Profiler::start("Less", __FUNCTION__.implode(', ', $files));
		}

		if (is_string($files))
		{
			$files = [$files];
		}

		// return comment if array is empty
		if (empty($files)) return self::_html_comment('no less files');

		$stylesheets = [];
		$assets = [];

		// validate
		foreach ($files as $file)
		{
			if (file_exists($file))
			{
				array_push($stylesheets, $file);
			}
			elseif (file_exists($file.self::$ext))
			{
				array_push($stylesheets, $file.self::$ext);
			}
			else
			{
				array_push($assets, self::_html_comment('could not find '.Debug::path($file).self::$ext));
			}
		}

		// all stylesheets are invalid
		if ( ! count($stylesheets)) return self::_html_comment('all less files are invalid');

		// get less config
		$config = Kohana::$config->load('less');

		// Clear compiled folder?
		if ($config['clear_first']) {
			self::clear_folder($config['path']);
		}

		$filenames = []; // used when config[combine]
		foreach ($stylesheets as $file)
		{
			$filename = self::_get_filename($file, $config['path'], $config['clear_first']);
			if (!$config['combine']) {
				$assets[] = HTML::style($filename, ['media' => $media]);
				continue;
			}
			$filenames[] = $filename;
		}

		if ($config['combine'])
		{
			$compressed = self::_combine($filenames);
			$assets[] =  HTML::style($compressed, ['media' => $media]);
		}

		$assets = implode("\n", $assets);

		if (isset($benchmark)) {
			Profiler::stop($benchmark);
		}

		return $assets;
	}

	/**
	 * Compress the css file
	 *
	 * @param   string   css string to compress
	 * @return  string   compressed css string
	 */
	private static function _compress($data)
	{
		$data = preg_replace('~/\*[^*]*\*+([^/][^*]*\*+)*/~', '', $data);
		$data = preg_replace('~\s+~', ' ', $data);
		$data = preg_replace('~ *+([{}+>:;,]) *~', '$1', trim($data));
		$data = str_replace(';}', '}', $data);
		$data = preg_replace('~[^{}]++\{\}~', '', $data);

		return $data;
	}

	/**
	 * Check if the asset exists already, if not generate an asset
     *
     * @param string  $file        The filename to check.
	 * @param string  $path        The path of the css file.
     * @param boolean $clear_first If we should clear the provided folder first.
	 * @return  string   path to the asset file
	 */
	protected static function _get_filename($file, $path, $clear_first)
	{
		// get the filename
		$filename = preg_replace('/^.+\//', '', $file);

		// get the last modified date
		$last_modified = self::_get_last_modified(array($file));

		// compose the expected filename to store in /media/css
		$compiled = $filename.'-'.$last_modified.'.css';

		// compose the expected file path
		$filename = $path.$compiled;

		// if the file exists no need to generate
		if ( ! file_exists($filename))
		{
			touch($filename, filemtime($file) - 3600);

			$config = Kohana::$config->load('less');
			$parser = new Less_Parser($config['options']);
			$parser->parseFile($file);
			$css = $parser->getCss();
			file_put_contents($filename, $css);
		}

		return $filename;
	}

	/**
	 * Combine the files
	 *
	 * @param   array    array of asset files
	 * @return  string   path to the asset file
	 */
	protected static function _combine($files)
	{

		// get the most recent modified time of any of the files
		$last_modified = self::_get_last_modified($files);

		// compose the asset filename
		$compiled = md5(implode('|', $files)).'-'.$last_modified.'.css';

		// compose the path to the asset file
		$config = Kohana::$config->load('less');
		$filename = $config['path'].$compiled;

		// if the file exists no need to generate
		if ( ! file_exists($filename))
		{
			self::_generate_assets($filename, $files);
		}

		return $filename;
	}

	/**
	 * Generate an asset file
	 *
	 * @param   string   filename of the asset file
	 * @param   array    array of source files
	 */
	protected static function _generate_assets($filename, $files)
	{
		// create data holder
		$data = '';

		touch($filename);

		ob_start();

		foreach($files as $file)
		{
			$data .= file_get_contents($file);
		}

		echo $data;

		file_put_contents($filename, ob_get_clean(), LOCK_EX);

		self::_compile($filename);
	}

	/**
	 * Compiles the file from less to css format
	 *
	 * @param   string   path to the file to compile
	 */
	public static function _compile($filename)
	{
		$config = Kohana::$config->load('less');
		$parser = new Less_Parser($config['options']);

		try
		{
			$compiled = $parser->parseFile($filename);
			$css = $parser->getCss();
			if ($config['compress']) {
				$compressed = self::_compress($css);
			}
			file_put_contents($filename, $compressed);
		}
		catch (LessException $ex)
		{
			exit($ex->getMessage());
		}
	}

	/**
	 * Get the most recent modified date of files
	 *
	 * @param   array    array of asset files
	 * @return  string   path to the asset file
	 */
	protected static function _get_last_modified($files)
	{
		$last_modified = 0;

		foreach ($files as $file)
		{
			$modified = filemtime($file);
			if ($modified !== false and $modified > $last_modified) $last_modified = $modified;
		}

		return $last_modified;
	}

	/**
	 * Format string to HTML comment format
	 *
	 * @param   string   string to format
	 * @return  string   HTML comment
	 */
	protected static function _html_comment($string = '')
	{
		return '<!-- '.$string.' -->';
	}

    /**
     * Delete all files from a provided folder.
     *
     * @param string $path The path to clear.
     *
     * @return void
     */
    private static function clear_folder($path) {
        $files = glob("$path*");
        foreach ($files as $file){
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
