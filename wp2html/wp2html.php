<?php
/*
Plugin Name: wp2html
Description: Make static HTMLs from WordPress
Author: Digital Acorn
Version: 1.0.2
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) || !isset( $_SERVER['SERVER_ADDR'] )) {
	exit;
}

define( 'WP2HTML_VERSION',        '1.0.2' );
define( 'WP2HTML_MENU_SLUG',      'wordpress2html' );
define( 'WP2HTML_PLUGIN_DIR',     plugin_dir_path( __FILE__ ) );
define( 'WP2HTML_PLUGIN_NAME',    'wp2html' );
define( 'WP2HTML_PLUGIN_TITLE',   'Make static HTML from WordPress' );
define( 'WP2HTML_DOCUMENT_ROOT',  $_SERVER['DOCUMENT_ROOT']);
define( 'WP2HTML_CONNECT_SERVER', $_SERVER['SERVER_ADDR']);
define( 'WP2HTML_OPTION_NAME',    'wp2html_options' );
define( 'WP2HTML_DEFAULT_PAGED',  'page/__page__' );
define( 'WP2HTML_USER_AGENT',     'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36' );

if ( ! class_exists( 'wp2html_main' ) ) {
	require_once WP2HTML_PLUGIN_DIR . 'classes/main.class.php';
}

// Check the curl
// $wp2html_error = wp2html_main::check_the_curl();
// if ( '' !== $wp2html_error ) {
// 	define( 'WP2HTML_ERROR_MESSAGE', $wp2html_error );
// }

if ( ! class_exists( 'wp2html_admin' ) ) {
	require_once WP2HTML_PLUGIN_DIR . 'classes/admin.class.php';
}
if ( ! class_exists( 'wp2html_create_links' ) ) {
	require_once WP2HTML_PLUGIN_DIR . 'classes/create_links.class.php';
}

add_action( 'admin_menu', array( 'wp2html_main', 'add_menu' ) );
add_action( 'admin_head-toplevel_page_wordpress2html', array( 'wp2html_admin', 'add_css') );
add_action( 'wp2html_action_when_the_post_is_saved', array( 'wp2html_main', 'on_save_post' ), 10, 2 );
