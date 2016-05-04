<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Less {

    public $config = [];
    // Default less files extension
    public static $ext = '.less';

    public function __construct($config = null) {

        $default_options = Kohana::$config->load('less');
        $this->config = $default_options;
    }

    public static function compile($files = '', $media = 'screen')
    {
      $less = new Less;
      return $less->c($files, $media);
    }
    /**
     * Get the link tag of less paths
     *
     * @param   mixed     array of css paths or single path
     * @param   string    value of media css type
     */
    public function c($files = '', $media = 'screen')
    {
        if (Kohana::$profiling) {
            $benchmark = Profiler::start("Less", __METHOD__);
        }

        if (is_string($files)) {
            $files = [$files];
        }

        // return comment if array is empty
        if (empty($files)) {
            return $this->_html_comment('no less files');
        }

        $stylesheets = [];
        $assets = [];

        // validate
        foreach ($files as $file) {
            if (file_exists($file)) {
                array_push($stylesheets, $file);
            }
            elseif (file_exists($file.self::$ext)) {
                array_push($stylesheets, $file.self::$ext);
            }
            else {
                array_push($assets, $this->_html_comment('could not find '.Debug::path($file).self::$ext));
            }
        }

        // all stylesheets are invalid
        if (!count($stylesheets)) {
            return $this->_html_comment('all less files are invalid');
        }

        // get less config

        // Clear compiled folder?
        if ($this->config['clear_first']) {
            $this->clear_folder($this->config['path']);
        }

        $filenames = []; // used when config[compress]
        foreach ($stylesheets as $file) {
            $filename = $this->_get_filename($file, $this->config['path'], $this->config['clear_first']);
            $filenames[] = $filename;
        }

        if ($this->config['combine']) {
            $combined = $this->get_combined_filename($filenames);
            $filenames = [$combined];
        }

        foreach ($filenames as $filename) {
            $assets[] =  HTML::style($filename, array('media' => $media));
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
    private function _compress($data)
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
    protected function _get_filename($file, $path, $clear_first)
    {
        if (Kohana::$profiling) {
            $benchmark = Profiler::start("Less", __METHOD__);
        }

        // get the last modified date
        $last_modified = $this->_get_last_modified(array($file));

        $filename = $this->get_compiled_filename($file, $last_modified);

        // compose the expected file path

        $css_modified = null;
        if (file_exists($filename)) {
            $css_modified = filemtime($filename);
        }
        // if the file exists no need to generate
        if (! file_exists($filename) || $last_modified > $css_modified) {
            touch($filename, filemtime($file) - 3600);
            if ($this->config['clear_old_files']) {
                $this->clear_files([$filename]);
            }

            // todo : do not filename,output css all in once without writing files
            $parser = new Less_Parser($this->config['options']);
            $parser->parseFile($file);
            $css = $parser->getCss();
            file_put_contents($filename, $css);
        }

        if (isset($benchmark)) {
            Profiler::stop($benchmark);
        }
        return $filename;
    }

    /**
     * Combine the files
     *
     * @param   array    array of asset files
     * @return  string   path to the asset file
     */
    protected function get_combined_filename($files)
    {
        if (Kohana::$profiling) {
            $benchmark = Profiler::start("Less", __METHOD__);
        }
        // get assets' css config

        // get the most recent modified time of any of the files
        $last_modified = $this->_get_last_modified($files);

        $filename = $this->get_compiled_filename($files, $last_modified);


        // if the file exists no need to generate
        if ( ! file_exists($filename)) {
            if ($this->config['clear_old_files']) {
                $this->clear_files([$filename]);
            }
            $this->combine($filename, $files);
        }

        if (isset($benchmark)) {
            Profiler::stop($benchmark);
        }
        return $filename;
    }

    /**
     * Generate an asset file
     *
     * @param   string   filename of the asset file
     * @param   array    array of source files
     */
    protected function combine($filename, $files)
    {
        // create data holder
        $data = '';

        touch($filename);

        ob_start();

        foreach($files as $file) {
            $data .= file_get_contents($file);
        }

        echo $data;

        file_put_contents($filename, ob_get_clean(), LOCK_EX);

        $this->_compile($filename);
    }

    /**
     * Compiles the file from less to css format
     *
     * @param   string   path to the file to compile
     */
    public function _compile($filename)
    {
        $config = Kohana::$config->load('less');
        $parser = new Less_Parser($config['options']);

        try {
            $compiled = $parser->parseFile($filename);
            $css = $parser->getCss();
            if ($this->config['compress']) {
                $compressed = $this->_compress($css);
            }
            file_put_contents($filename, $compressed);
        }
        catch (LessException $ex) {
            exit($ex->getMessage());
        }
    }

    /**
     * Get the most recent modified date of files
     *
     * @param   array    array of asset files
     * @return  string   path to the asset file
     */
    protected function _get_last_modified($files)
    {
        if (Kohana::$profiling) {
            $benchmark = Profiler::start("Less", __METHOD__);
        }
        $last_modified = 0;

        foreach ($files as $file) {
            $modified = filemtime($file);
            if ($modified !== false and $modified > $last_modified) $last_modified = $modified;
        }

        if (isset($benchmark)) {
            Profiler::stop($benchmark);
        }
        return $last_modified;
    }

    /**
     * Format string to HTML comment format
     *
     * @param   string   string to format
     * @return  string   HTML comment
     */
    protected function _html_comment($string = '')
    {
        return '<!-- '.$string.' -->';
    }

    /**
     * Delete files before check compile (force recompile each time)
     *
     * @param string $path The path to clear.
     *
     * @return void
     */
    private function clear_files($required_files) {
        $path = $this->config['path'];

        $files = glob("${path}*.css");

        if ($this->config['timestamp_in_filename']) {
            //$regex_suffix = '(?:-\d{10})?';
            $regex_suffix = '(?:-\d{10})';
        } else {
            $regex_suffix = '';
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($required_files) {
                    foreach($required_files as $req_file) {
                        // remove timestamp from the filename
                        $req_file = basename($req_file);
                        $req_file = preg_replace('#-\d{10}\.css#', '', $req_file);
                        if (preg_match('#'.preg_quote($path.$req_file).$regex_suffix.'\.css#', $file)) {
                            unlink($file);
                            break;
                        }
                    }
                } else {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Delete all files from the config $path given
     *
     * @param string $path The path to clear.
     *
     * @return void
     */
    public function clear_folder() {
        return $this->clear_files([]);
    }

    public function get_compiled_filename($files, $last_modified)
    {
        static $count = 0;
        if (is_array($files)) {
            $filename = '';
            // compose the asset filename
            if ($this->config['combine_filename']) {
                $filename = $this->config['combine_filename'];
                if ($count > 0) {
                    $filename .= $count;
                }
                $count++;
            } else {
                $filename .= md5(implode('|', $files));
            }
        } else {

            // get the filename
            $filename = basename($files);
            $filename = str_replace(self::$ext, '', $filename);

            // compose the expected filename to store in /media/css
        }

        if ($this->config['timestamp_in_filename']) {
            $filename .= '-'.$last_modified;
        }

        $filename .= '.css';

        // compose the path to the asset file
        $filename = $this->config['path'].$filename;
        return $filename;
    }
}
