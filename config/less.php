<?php defined('SYSPATH') OR die('No direct access allowed.');

return [
	'path'                  => 'css/', // destination path (relative to DOCUMENT_ROOT) to store compiled / compressed css
	'clear_first'           => false,  // Clear the provided folder before writing new file
	'combine'               => false,  // combine multiple files into one
	'combine_filename'      => false,  // output filename custom when combine=true (instead of md5)
	'compress'              => false,  // compress even more (after less.php output)

	'timestamp_in_filename' => true,   // if true, output filename will contains timestamp and it will be used to check modified time instead of mtime
	'clear_old_files'       => true,   // if timestamp_in_filename is true, this will remove the previously generated files containing timestamp. set to false for better performance

	'options' => [
		'compress'           => false, // option - whether to compress

		'strictUnits'        => false, // whether units need to evaluate correctly
		'strictMath'         => false, // whether math has to be within parenthesis
		'relativeUrls'       => true,  // option - whether to adjust URL's to be relative
		'urlArgs'            => '',    // whether to add args into url tokens
		'numPrecision'       => 8,

		'import_dirs'        => [],
		'import_callback'    => null,

		'cache_dir'          => null,
		'cache_method'       => 'php', // false, 'serialize', 'php', 'var_export', 'callback'
		'cache_callback_get' => null,
		'cache_callback_set' => null,

		'sourceMap'          => false, // whether to output a source map
		'sourceMapBasepath'  => null,
		'sourceMapWriteTo'   => null,
		'sourceMapURL'       => null,

		'indentation'        => '  ',
		'plugins'            => [],

	],
];
