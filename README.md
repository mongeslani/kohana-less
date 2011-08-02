KO3 LESS Module v.1.1.1
=======================

LESS Module is a port of Leaf Corcoran's [LESSPHP](http://leafo.net/lessphp) for Kohana 3
It adopts some of Alex Sancho's Kohana 2.3 Assets Module codes for CSS compression, credits goes to them
Thanks to [cheeaun](http://github.com/cheeaun) for helping out!
You might also want to check out another implementation from [jeremeamia](http://github.com/jeremeamia/kohana-less).

To Use
-------
1. Put the less module folder in your Modules directory
2. Include less module in your application's bootstrap: 'less' => MODPATH.'less'
3. Copy the less config file from /modules/less/config/less.php to your application's config directory
4. From your less.php config file, put the 'path' to where you want the CSS files compiled / compressed, the folder must be writable
5. You can set 'compress' to TRUE on your less.php config file if you want your CSS files to be combined in to one file and compressed (to lessen server calls)

Sample Code
------------

Default less files extension is set into `Less::$extension` and is `.less`.


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
			// path below will be treated as: DOCROOT . 'media/css/'
			'path'     => 'media/css/',
			'compress' => TRUE,
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
			<?= $stylesheet; // will give me ONE compressed css file located in /media/css/ ?>
		</head>
		<body>
			<h1>LESS for Kohana or Kohana for LESS?</h1>
		</body>
		</html>

Issues
-------
Please report it to the [issues tracker](http://github.com/mongeslani/kohana-less/issues).