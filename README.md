KO3 LESS Module v.1.3
=======================

Thanks to the previous authors:
[mongeslani](http://github.com/mongeslani), [cheeaun](http://github.com/cheeaun) and [jeremeamia](http://github.com/jeremeamia/kohana-less)

This module is based on the "KO3 LESS Module v.1.1.1" which can be found at https://github.com/mongeslani/kohana-less

Quick start
-----------

Installation:

Enable this module like a common kohana module.
Copy the file `modules/less/config/less.php`
to `application/config/less.php` and customize the `path` config (and any
    other options you wants).

Usage:

    <?php
        $less_files = ['APPPATH.'media/less/style', 'APPPATH.'media/less/otherfile'];
        $stylesheet = Less::compile($less_files);
        // output : <link src="/css/style.less-1461086885.css" />


* clone that repository into `modules/less` (use `--recursive`) `Kohana::modules(['less' => 'modules/less']);` into your
`application/bootstrap.php` file
* copy modules/less/config


Installation
------------

Command Line:
    $ git submodule update --init --recursive https://github.com/Asenar/kohana-less modules/less
    $ cp modules/less/config/less.php application/config/less.php
    $ echo "Kohana::modules(['less' => 'modules/less']);" >> application/bootstrap.php
    # or edit application/bootstrap.php and enable the module

Sample Code
------------

Default less files extension is set in `Less::$extension` and is `.less`.

** MODPATH/baseModule/media/css/layout.less **

		@bodyBkgColor: #EEE;

		body {
			background: @bodyBkgColor;
			margin:0;
			padding:0;

			h1 { font-size: 3em; }
		}

** APPPATH/media/css/style.less **

		@divBkgColor: #DDD;

		.roundedCorners (@radius:8px) {
			-moz-border-radius:@radius;
			-webkit-border-radius:@radius;
			border-radius:@radius;
			zoom:1;
		}

		div {
			background: @divBkgColor;
			.roundedCorners;

			p { .roundedCorners(5px); }
		}

** APPPATH/config/less.php **

		return array(
			// relative PATH to a writable folder to store compiled / compressed css
			// path below will be treated as: DOCROOT . 'css/'
			'path'     => 'css/',
			'combine'  => true,
		);

** In your controller **

		class Controller_Sample extends Controller_Template {

			public $template = 'template';

			public function action_example1()
			{
				// no need to add .less extension
				// you can put your less files anywhere
				$less_files = array
				(
					MODPATH.'baseModule/media/css/layout.less',
					APPPATH.'media/css/style',
				);

				$this->template->stylesheet = Less::compile($less_files);
			}

			public function action_example2()
			{
				// you can pass just single file
				
				$this->template->stylesheet = Less::compile(APPPATH.'media/css/style');
			}
		}

** In your template **

		<html>
		<head>
			<title>LESS for Kohana</title>
			<?php echo $stylesheet; // will give me ONE compressed css file located in /css/ ?>
		</head>
		<body>
			<h1>LESS for Kohana or Kohana for LESS?</h1>
		</body>
		</html>

Bug/Requests
-------
Please report it to the [issues tracker](http://github.com/Asenar/kohana-less/issues)..
