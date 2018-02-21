<?php
/*
Plugin Name: Custom post type to Help
Plugin URI: http://zeidan.info
Description: Allow to add help tags form a help CPT and select the page to be shown.
Author: Eric Zeidan
Author URI: http://zeidan.info
Version: 0.0.1
Text Domain: ctptohelp
*/

define('CPTTEXDOMAIN', 'ctptohelp');
define('CPTBASEDIR', plugin_dir_url(__FILE__));

foreach (glob(__DIR__ . "/inc/class-*.php") as $filename)
	require_once($filename);

$cptt = new cptToHelp();

register_activation_hook( __FILE__, array( $cptt, 'cptToHelpInstall' ) );