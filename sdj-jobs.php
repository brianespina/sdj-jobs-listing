<?php

/*
Plugin Name: SDJ jobs 
Plugin URI: https://www.servicedesignjobs.com/
Description: A plugin for SDJ joblisting and filtering
Author: Brian Espina
Author URI: https://www.servicedesignjobs.com/
Version: 1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

require_once( __DIR__ . '/includes/SvelteWP.php' );
require_once( __DIR__ . '/includes/shortcodes.php' );
require_once( __DIR__ . '/includes/ajax_endpoints.php' );

if ( class_exists( 'SvelteWP' ) ) {
	$svelteWP = new SvelteWP();
}