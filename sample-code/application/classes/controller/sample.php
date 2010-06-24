<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Sample extends Controller_Template {

	public $template = 'template';

	public function action_index()
	{
		/**
		 *
		 * No need to add .less extension, but if you REALLY want to it should still work.
		 * You can put your less files anywhere, APPPATH or MODPATH...
		 * I like to put my media files (less / css / js) on my applications for cleaner organization.
		 * Another trick is to use Kohana::find_file('media', '/less/filename', 'less') so you can abuse cascading.
		 *
		 */
		$less_files = array
		(
			APPPATH.'media/less/layout',
			APPPATH.'media/less/style',
		);

		$this->template->stylesheet = Less::compile($less_files);
	}
}