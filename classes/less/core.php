<?php defined('SYSPATH') or die('No direct script access.');

class Less_Core
{
	/**
	 * Get the link tag of less paths
	 *
	 * @param   array     array of css paths
	 * @param   string    value of media css type
	 * @param   boolean   allow compression
	 * @return  string    link tag pointing to the css paths
	 */
	public static function compile($array = array(), $media = 'screen')
	{
		// return comment if array is empty
		if (empty($array)) return self::_html_comment('no less files');

		$stylesheets = array();
		$assets = array();

		// validate
		foreach ($array as $file)
		{
			// remove extension if its present
			$file = preg_replace('/\.less/', '', $file);

			if (file_exists($file.'.less'))
			{
				array_push($stylesheets, $file);
			}
			else
			{
				array_push($assets, self::_html_comment('could not find '.Kohana::debug_path($file).'.less'));
			}
		}

		// all stylesheets are invalid
		if ( ! count($stylesheets)) return self::_html_comment('all less files are invalid');

		// get less config
		$config = Kohana::config('less');

		// if compression is allowed
		if ($config['compress'])
		{
			return html::style(self::_combine($stylesheets), array('media' => $media));
		}

		// if no compression
		foreach ($stylesheets as $file)
		{
			$filename = self::_get_filename($file, $config['path']);
			array_push($assets, html::style($filename, array('media' => $media)));
		}

		return implode("\n", $assets);
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
	 * @param   string   path of the css file
	 * @return  string   path to the asset file
	 */
	protected static function _get_filename($file, $path)
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
			// create data holder
			$data = '';

			touch($filename, filemtime($file.'.less') - 3600);

			lessc::ccompile($file.'.less', $filename);
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
		// get assets' css config
		$config = Kohana::config('less');

		// get the most recent modified time of any of the files
		$last_modified = self::_get_last_modified($files);

		// compose the asset filename
		$compiled = md5(implode('|', $files)).'-'.$last_modified.'.css';

		// compose the path to the asset file
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
			$data .= file_get_contents($file.'.less');
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
		$less = new lessc($filename);

		try
		{
			$compiled = $less->parse();
			$compressed = self::_compress($compiled);
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
			$modified = filemtime($file.'.less');
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
}