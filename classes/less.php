<?php defined('SYSPATH') or die('No direct script access.');

class Less
{
  
	public static function set($files, $media = 'screen', $compress = NULL)
	{
		$config = Kohana::config('less');
		if ($compress == NULL) $compress = $config->compress;

		if ( ! $compress)
		{
			$stylesheets = array();

			foreach ($files as $input)
			{
				$filename = substr($input, strripos('/' . $input, '/'), strlen($input));
				$output = self::get_output($filename);
				array_push($stylesheets, html::style( self::compile($input, $output), array('media' => $media) ));
			}

			return implode("\n", $stylesheets);
		}

		return html::style( self::glue($files), array('media' => $media) );
	}

	public static function get_output($filename)
	{
		$config = Kohana::config('less');
		$filepath = $config->path . $filename;

		if ( ! file_exists($filepath . '.css'))
		{
			touch($config->path . $filename . '.css', time() - 3600);
		}

		return $filename;
	}

	public static function glue($files)
	{
		$config = Kohana::config('less');
		$files_lastmodified = self::get_last_modified($files);
		$filename = md5(implode('|', $files)) . '-' .  $files_lastmodified;
		$filepath = $config->path . $filename;

		if ( ! file_exists($filepath . ' .css') OR filemtime($filepath . '.css') < $files_lastmodified )
		{
			ob_start();
			foreach($files as $file)
			{
				echo file_get_contents($file . '.less');
			}
			file_put_contents($filepath . '.css', ob_get_clean(), LOCK_EX);
			self::compile_compress($filepath);
		}

		return $filepath . '.css';
	}

	public static function compile($input, $output)
	{
		$config = Kohana::config('less');

		try
		{
			lessc::ccompile($input . '.less', $config->path . $output . '.css');
		}
		catch (LessException $ex)
		{
			exit($ex->getMessage());
		}

		return $config->path . $output . '.css';
	}

	public static function compile_compress($filepath)
	{
		$less = new lessc($filepath . '.css');

		try
		{
			$compiled = $less->parse();
			$compressed = self::compress($compiled);
			file_put_contents($filepath . '.css', $compressed);
		}
		catch (LessException $ex)
		{
			exit($ex->getMessage());
		}
	}

	private static function compress($data)
	{
		$data = preg_replace('~/\*[^*]*\*+([^/][^*]*\*+)*/~', '', $data);
		$data = preg_replace('~\s+~', ' ', $data);
		$data = preg_replace('~ *+([{}+>:;,]) *~', '$1', trim($data));
		$data = str_replace(';}', '}', $data);
		$data = preg_replace('~[^{}]++\{\}~', '', $data);
		return $data;
	}

	private static function get_last_modified($files)
	{
		$last_modified = 0;

		foreach ($files as $file) 
		{
			$modified = filemtime($file . '.less');
			if ($modified !== false and $modified > $last_modified) $last_modified = $modified;
		}

		return $last_modified;
	}
}
