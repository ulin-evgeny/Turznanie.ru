<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * File-based configuration reader. Multiple configuration directories can be
 * used by attaching multiple instances of this class to [Kohana_Config].
 *
 * @package    Kohana
 * @category   Configuration
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Config_File_Reader implements Kohana_Config_Reader {

    /**
     * The directory where config files are located
     *
     * @var string
     */
    protected $_directory = '';

    protected $_default_directory = CONFIG_FOLDER_DEFAULT;

    /**
     * Creates a new file reader using the given directory as a config source
     *
     * @param string $directory Configuration directory to search
     */
    public function __construct($directory = CONFIG_FOLDER)
    {
        // Set the configuration directory name
        $this->_directory = trim($directory, '/');
    }

    /**
     * Load and merge all of the configuration files in this group.
     *
     *     $config->load($name);
     *
     * @param   string $group configuration group name
     * @return  $this   current object
     * @uses    Kohana::load
     */
    public function load($group)
    {
        $config = array();

        $is_dir = false;

        if ($this->_directory !== CONFIG_FOLDER_DEFAULT) {
            $dir = realpath(APPPATH . $this->_directory);
            $is_dir = is_dir($dir);

            // Проверяем существование папки. Если существует, то получаем конфиги.
            if ($is_dir) {
                $files = Kohana::find_file($this->_directory, $group, null, true);

                // Если конфигов в данной папке нет, то проверим их наличие в папке с конфигами по умолчанию (может же быть такое, что какой-то конфиг ничем не отличается в разных версиях (dev, prod, default), поэтому есть только в папке с конфигами по умолчанию)
                $default_directory_checked = false;
                if (empty($files)) {
                    $files = Kohana::find_file($this->_default_directory, $group, null, true);
                    $default_directory_checked = true;
                }


                if ($default_directory_checked === false) {
                    // Некоторые конфиги содержатся в других местах, например: modules/auth/config. Поэтому надо поискать еще и там. Исключая application/config.
                    $path_to_config = str_replace($this->_directory, '', $dir);
                    $other_files = Kohana::find_file($this->_default_directory, $group, null, true);
                    foreach ($other_files as $file) {
                        if (mb_strpos($file, $path_to_config) === false) {
                            $config = Arr::merge($config, Kohana::load($file));
                        }
                    }
                }
            }
        }

        if (!$is_dir) {
            $files = Kohana::find_file($this->_default_directory, $group, null, true);
        }

        if (!empty($files)) {
            foreach ($files as $file) {
                // Merge each file to the configuration array
                $config = Arr::merge($config, Kohana::load($file));
            }
        }

        return $config;
    }

}
