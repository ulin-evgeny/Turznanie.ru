<?php

defined('SYSPATH') or die('No direct script access.');

// -- Environment setup --------------------------------------------------------
// Load the core Kohana class
require SYSPATH . 'classes/Kohana/Core' . EXT;

if (is_file(APPPATH . 'classes/Kohana' . EXT)) {
	// Application extends the core
	require APPPATH . 'classes/Kohana' . EXT;
} else {
	// Load empty core extension
	require SYSPATH . 'classes/Kohana' . EXT;
}

// Подключение библиотек
require('libs/PHPMailer/src/PHPMailer.php');
require('libs/PHPMailer/src/Exception.php');
require('libs/PHPMailer/src/SMTP.php');
require('libs/ReCaptcha/ReCaptcha.php');
require('libs/HTMLPurifier/library/HTMLPurifier.auto.php');

/**
 * Set the default time zone.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/timezones
 */
date_default_timezone_set('Etc/GMT-3');

/**
 * Set the default locale.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/function.setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @link http://kohanaframework.org/guide/using.autoloading
 * @link http://www.php.net/manual/function.spl-autoload-register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Optionally, you can enable a compatibility auto-loader for use with
 * older modules that have not been updated for PSR-0.
 *
 * It is recommended to not enable this unless absolutely necessary.
 */
//spl_autoload_register(array('Kohana', 'auto_load_lowercase'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @link http://www.php.net/manual/function.spl-autoload-call
 * @link http://www.php.net/manual/var.configuration#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-us');

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (IN_PRODUCTION) {
	Kohana::$environment = Kohana::PRODUCTION;
} else {
	Kohana::$environment = Kohana::DEVELOPMENT;
}

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - integer  cache_life  lifetime, in seconds, of items cached              60
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 * - boolean  expose      set the X-Powered-By header                        FALSE
 */
Kohana::init(array(
	'base_url' => '/',
	'index_file' => FALSE,
	'errors' => !IN_PRODUCTION,
	'profile' => false,
	'caching' => IN_PRODUCTION,
	'cache_life' => 600
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
//Kohana::$log->attach(new Log_File(APPPATH . 'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 * (Kohana::$environment == Kohana::DEVELOPMENT)?MODPATH.'userguide':NULL
 */
Kohana::modules(array(
	'auth' => MODPATH . 'auth', // Basic authentication
	//'cache'      => MODPATH.'cache',      // Caching with multiple backends
	//'codebench'  => MODPATH.'codebench',  // Benchmarking tool
	'database' => MODPATH . 'database', // Database access
	'image' => MODPATH . 'image', // Image manipulation
	//'minion'     => MODPATH.'minion',     // CLI Tasks
	'orm' => MODPATH . 'orm', // Object Relationship Mapping
	//'unittest'   => MODPATH.'unittest',   // Unit testing
	'userguide' => MODPATH . 'userguide' // User guide and API documentation
));

// -----------------------------------------
// Определение некоторых настроек
// -----------------------------------------
// Информация о сайте
$config = Kohana::$config->load('site_info');
$SITE_INFO = $config['data'];

// Соль для куки
$config = Kohana::$config->load('cookie');
Cookie::$salt = $config['salt'];

// Капча
$config = Kohana::$config->load('recaptcha');
define('CHECK_CAPTCHA', $config['enabled']);

// Очищаем переменную
unset($config);
// -----------------------------------------


/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 *
 * Роуты достаются из БД и заносятся в массив. Это сделано для того, чтобы можно было легко что-то поменять в БД и не пришлось переписывать код здесь. Здесь, да и в целом на сайте - эта переменная глобальная и ее можно использовать в if, в switch и т.д.
 */
$aliases = array(
	'admin' => Model_Seo::get_alias_by_id(Model_Seo::ID_ADMIN_PANEL, false),
	'seo' => Model_Seo::get_alias_by_id(Model_Seo::ID_CATALOG_SEO, false),
	'tags' => Model_Seo::get_alias_by_id(Model_Seo::ID_CATALOG_TAGS, false),
	'authors' => Model_Seo::get_alias_by_id(Model_Seo::ID_CATALOG_AUTHORS, false),
	'users' => Model_Seo::get_alias_by_id(Model_Seo::ID_CATALOG_USERS, false),
	'comments' => Model_Seo::get_alias_by_id(Model_Seo::ID_CATALOG_COMMENTS, false),
	'literature' => Model_Seo::get_alias_by_id(Model_Seo::ID_CATALOG_LITERATURE, false),
	'articles' => Model_Seo::get_alias_by_id(Model_Seo::ID_CATALOG_ARTICLES, false),
	'news' => Model_Seo::get_alias_by_id(Model_Seo::ID_CATALOG_NEWS, false),
	'contact_us' => Model_Seo::get_alias_by_id(Model_Seo::ID_CONTACT_US, false),
	'about' => Model_Seo::get_alias_by_id(Model_Seo::ID_ABOUT, false),
	'partnership' => Model_Seo::get_alias_by_id(Model_Seo::ID_PARTNERSHIP, false),
	'user' => Model_Seo::get_alias_by_id(Model_Seo::ID_USER, false),
	'cabinet' => Model_Seo::get_alias_by_id(Model_Seo::ID_CABINET, false),
	'search' => Model_Seo::get_alias_by_id(Model_Seo::ID_SEARCH, false),
	'registration' => Model_Seo::get_alias_by_id(Model_Seo::ID_REGISTRATION, false),
	'unsubscribe' => Model_Seo::get_alias_by_id(Model_Seo::ID_UNSUBSCRIBE, false),
	'agreement' => Model_Seo::get_alias_by_id(Model_Seo::ID_AGREEMENT, false)
);

Route::set('seo', $aliases['admin'] . '/' . $aliases['seo'] . '/<page_id>(/<part1>(/<part2>(/<part3>(/<part4>(/<part5>)))))', array('page_id' => '[0-9]*'))
	->defaults(array(
		'directory' => 'Admin',
		'controller' => 'Seo',
		'action' => 'index'
	));
Route::set('admin_panel', $aliases['admin'] . '/<alias>(/<part1>(/<part2>(/<part3>(/<part4>))))', array('alias' => $aliases['tags'] . '|' . $aliases['authors'] . '|' . $aliases['users'] . '|' . $aliases['seo'] . '|' . $aliases['comments']))
	->defaults(array(
		'directory' => 'Admin',
		'controller' => 'Catalog',
		'action' => 'index'
	));
Route::set('catalog', '<alias>(/<part1>(/<part2>(/<part3>(/<part4>))))', array('alias' => $aliases['literature'] . '|' . $aliases['articles'] . '|' . $aliases['news']))
	->defaults(array(
		'controller' => 'Catalog',
		'action' => 'index',
	));
Route::set('contact_us', $aliases['contact_us'])
	->defaults(array(
		'controller' => 'ContactUs',
		'action' => 'index'
	));
Route::set('info_page', '<page_url>', array('page_url' => $aliases['about'] . '|' . $aliases['partnership'] . '|' . $aliases['agreement']))
	->defaults(array(
		'controller' => 'InfoPage',
		'action' => 'index'
	));
Route::set('user', $aliases['user'] . '(/<part1>(/<action>(/<part>)))')
	->defaults(array(
		'controller' => 'User',
		'action' => 'index',
	));
Route::set('cabinet', $aliases['cabinet'] . '(/<action>(/<part>))')
	->defaults(array(
		'controller' => 'Cabinet',
		'action' => 'index',
	));
Route::set('ajax', 'ajax(/<action>)')
	->defaults(array(
		'controller' => 'Ajax',
		'action' => 'index'
	));
Route::set('search', $aliases['search'] . '(/<search_type>)')
	->defaults(array(
		'controller' => 'Search',
		'action' => 'index'
	));
Route::set('users', '<action>', array('action' => 'approve|' . $aliases['registration'] . '|need_captcha_bool|login|login_handle|logout|validate|forgot|restore|change_email|request_approve_mail|unsubscribe'))
	->defaults(array(
		'controller' => 'Users',
	));
Route::set('default', '(<controller>(/<action>(/<id>)))')
	->defaults(array(
		'controller' => 'Index',
		'action' => 'index'
	));