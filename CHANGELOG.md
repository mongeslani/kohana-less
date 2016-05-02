2.0
---
New:
* config `timestamp_in_filename` (default:true)
		if set to false, the output file will only contains the default filename
		without appending the timestamp. With this method, checking the datetime
		of files is done with the php function `filemtime`, which can be a little
		longer. When combining, this option is ignored (for now).
* non static method `$less->c($files, $media)`
* new method `$less->clear_files($files)`
* `$less->clear_folder()` uses clear_files([]).
* the file extension `.less` is now removed from compiled filename.

Changes:
* most of static private/proteced method replaced by non-static.
		 _compress, _get_filename, _combine, _generate_assets, _html_comment,
		 _get_last__modified, _compile, clear_folder
* static method `Less::compile` uses the new method `$less->c`.
* more psr-2 code.
* Put the configuration in class property `Less::$config`
* config `clear_first`
		if true, all .css files will be removed from the configured `path`.
* `Kohana::$profiling` is more used

v.1.3
---
New:
* Use less.php v1.7.0.10
* Use `Less_Autoloader` without lessc.inc.php (breaks compatibility with
		leafo/lessphp)
* Use `new Less_Parser($options)` with options from `config/less` (the options key).
		By default,  config `options` contains all default from less.php
		See http://lessphp.typesettercms.com/ (https://github.com/oyejorge/less.php/blob/master/lib/Less/Parser.php)
* config `combine` (default: false)
		if true, the method `Less::compile($files)` will only write one file, with the
		md5sum of all files given as argument.
* config `compress` (default: false)
		if true, will compress css with regular expression (remove useless
				whitespaces, remove newlines, …).

Changes:
* more psr-2 code.
* the configuration `combine` will not longer compress css.

Fix:

v.1.2
---
New:
* Replace leafo/lessphp (not maintained since 2013) by oyejorge/less.php
* Use Kohana benchmark


Changes:
* Update directory structure
		to be like Kohana project (moved from `classes/` to `classes/Kohana/`)
* `Less::compile()` output without `\n` when multiple files.

