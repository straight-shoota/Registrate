<?php
/*
Plugin Name: Registrate Open Event Registration
Plugin URI: http://straight-shoota.de/registrate
Description:
Version: 0.1-alpha
Author: Johannes M�ller
Author URI: http://straight-shoota.de
*/
define('REGISTRATE_DIR', 'D:\srv\registrate\\');
//define('REGISTRATE_DIR', dirname(__FILE__) . "/");

//error_reporting(E_ALL);

ini_set('include_path', ini_get('include_path') . ';' . REGISTRATE_DIR);


require_once 'registrate-wordpress.php';