<?php

/*
 * Use the DS to separate the directories in other defines
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/*
 * These definitions should only be edited if you have cake installed in
 * a directory layout other than the way it is distributed.
 * When using custom settings be sure to use the DS and do not add a trailing DS.
 */


define('ROOT', dirname(__DIR__)); // The full path to the directory which holds "src", WITHOUT a trailing DS.
define('APP_DIR', 'src'); //The actual directory name for the application directory. Normally named 'src'
define('APP', ROOT . DS . APP_DIR . DS); // Path to the application's directory.
define('CONFIG', ROOT . DS . 'config' . DS); //Path to the config directory.

/*
 * File path to the webroot directory.
 *
 * To derive your webroot from your webserver change this to:
 *
 * `define('WWW_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'], DS) . DS);`
 */
define('WWW_ROOT', ROOT . DS . 'webroot' . DS);
define('TESTS', ROOT . DS . 'tests' . DS); // Path to the tests directory.
define('TMP', ROOT . DS . 'tmp' . DS); // Path to the temporary files directory.
define('LOGS', ROOT . DS . 'logs' . DS); // Path to the logs directory.
define('CACHE', TMP . 'cache' . DS); // Path to the cache files directory. It can be shared between hosts in a multi-server setup.
define('RESOURCES', ROOT . DS . 'resources' . DS); //Path to the resources directory.

/*
 * The absolute path to the "cake" directory, WITHOUT a trailing DS.
 *
 * CakePHP should always be installed with composer, so look there.
 */
define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS); //Path to the cake directory.
define('CAKE', CORE_PATH . 'src' . DS);
