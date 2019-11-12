<?php
// ------------------------------------------
// Технические работы
// ------------------------------------------
//header("HTTP/1.1 503 Service Unavailable");
//header("Cache-Control: no-cache,no-store,max-age=0,must-revalidate");
//header("Pragma: no-cache");
//require_once('/technical_works/index.php');
//exit(0);

/**
 * The directory in which your application specific resources are located.
 * The application directory must contain the bootstrap.php file.
 *
 * @link http://kohanaframework.org/guide/about.install#application
 */
$application = 'application';

/**
 * The directory in which your modules are located.
 *
 * @link http://kohanaframework.org/guide/about.install#modules
 */
$modules = 'modules';

/**
 * The directory in which the Kohana resources are located. The system
 * directory must contain the classes/kohana.php file.
 *
 * @link http://kohanaframework.org/guide/about.install#system
 */
$system = 'system';

/**
 * The default extension of resource files. If you change this, all resources
 * must be renamed to use the new extension.
 *
 * @link http://kohanaframework.org/guide/about.install#ext
 */
define('EXT', '.php');

// -----------------------------------------
// Определение пользовательских констант и некоторых настроек
// -----------------------------------------
// Продакшн? От этой переменной зависят кеширование, вывод ошибок и другое
const IN_PRODUCTION = false;

// Константа, в которой перечисляются наименования полей в БД, которые НЕ ДОЛЖНЫ проходить проверку на неразрешенные теги / их атрибуты
const WHITE_LIST_COLUMNS = array(
    'password',
    'token_approve',
    'token_restore',
    'token_change_email',
    'token_unsubscribe'
);

// Разрешенные на сайте теги и атрибуты
const ALLOWABLE_TAGS = 'p,strong,em,u,s,blockquote,a,br';
const ALLOWABLE_ATTRS = 'a.href,*.style';

// --------------------------------------
// Константа папки с конфигами и константы-значения для этой константы (чтобы удобнее было переключаться между значениями)
// --------------------------------------
/**
 * Директория с файлами конфигурации по умолчанию. Она захардкодена в некоторых файлах Kohana (например: \system\classes\Kohana\Core.php), поэтому ее лучше не менять.
 */
const CONFIG_FOLDER_DEFAULT = 'config';

const CONFIG_FOLDER_PROD = CONFIG_FOLDER_DEFAULT . DIRECTORY_SEPARATOR . 'config_prod';
const CONFIG_FOLDER_DEV = CONFIG_FOLDER_DEFAULT . DIRECTORY_SEPARATOR . 'config_dev';

/**
 * Константа определяет, какая папка отвечает за файлы конфигурации
 */
const CONFIG_FOLDER = CONFIG_FOLDER_DEFAULT;
// --------------------------------------

/**
 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
 *
 * @link http://www.php.net/manual/errorfunc.configuration#ini.error-reporting
 *
 * When developing your application, it is highly recommended to enable notices
 * and strict warnings. Enable them by using: E_ALL | E_STRICT
 *
 * In a production environment, it is safe to ignore notices and strict warnings.
 * Disable them by using: E_ALL ^ E_NOTICE
 *
 * When using a legacy application with PHP >= 5.3, it is recommended to disable
 * deprecated notices. Disable with: E_ALL & ~E_DEPRECATED
 */
if (IN_PRODUCTION) {
    error_reporting(0);
} else {
    error_reporting(E_ALL);
}

/**
 * End of standard configuration! Changing any of the code below should only be
 * attempted by those with a working knowledge of Kohana internals.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 */
// Set the full path to the docroot
define('DOCROOT', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);


// Make the application relative to the docroot, for symlink'd index.php
if (!is_dir($application) AND is_dir(DOCROOT . $application)) {
    $application = DOCROOT . $application;
}

// Make the modules relative to the docroot, for symlink'd index.php
if (!is_dir($modules) AND is_dir(DOCROOT . $modules)) {
    $modules = DOCROOT . $modules;
}

// Make the system relative to the docroot, for symlink'd index.php
if (!is_dir($system) AND is_dir(DOCROOT . $system)) {
    $system = DOCROOT . $system;
}

// Define the absolute paths for configured directories
define('APPPATH', realpath($application) . DIRECTORY_SEPARATOR);
define('MODPATH', realpath($modules) . DIRECTORY_SEPARATOR);
define('SYSPATH', realpath($system) . DIRECTORY_SEPARATOR);

// Define custom constants
$views = $application . DIRECTORY_SEPARATOR . 'views';
define('VIEWSPATH', realpath($views) . DIRECTORY_SEPARATOR);
$mails = $application . DIRECTORY_SEPARATOR . 'mails';
define('MAILSPATH', realpath($mails) . DIRECTORY_SEPARATOR);

// Define units
define('KB', 1024);
define('MB', 1048576);
define('GB', 1073741824);
define('TB', 1099511627776);

define('MYSQL_DATE_FORMAT', "Y-m-d H:i:s");

// Папки
define('FOLDER_IMAGES', 'images/');
define('FOLDER_FILES', 'files/');


// Clean up the configuration vars
unset($application, $modules, $system, $mails, $views);

if (file_exists('install' . EXT)) {
    // Load the installation check
    return include 'install' . EXT;
}

/**
 * Define the start time of the application, used for profiling.
 */
if (!defined('KOHANA_START_TIME')) {
    define('KOHANA_START_TIME', microtime(true));
}

/**
 * Define the memory usage at the start of the application, used for profiling.
 */
if (!defined('KOHANA_START_MEMORY')) {
    define('KOHANA_START_MEMORY', memory_get_usage());
}

// Bootstrap the application
require APPPATH . 'bootstrap' . EXT;

if (PHP_SAPI == 'cli') { // Try and load minion
    class_exists('Minion_Task') OR die('Please enable the Minion module for CLI support.');
    set_exception_handler(array(
        'Minion_Exception',
        'handler'
    ));

    Minion_Task::factory(Minion_CLI::options())->execute();
} else {
    /**
     * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
     * If no source is specified, the URI will be automatically detected.
     */
    echo Request::factory(true, array(), false)->execute()->send_headers(true)->body();
}