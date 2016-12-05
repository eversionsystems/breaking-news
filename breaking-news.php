<?php
/*
Plugin Name: Breaking News
Plugin URI: http://eversionsystems.com/
Description: Display a selected post as breaking news at the top of every page.
Version: 1.0.0
Author: Andrew Schultz
Author URI: http://eversionsystems.com
License: GPLv2 or later
Text Domain: breaking-news
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;	//Exit if accessed directly
}

/** @var string Set the plugin version. */
define( 'BN_VERSION', '1.0.0' );

/**
 * Register the hook to remove the plugins settings on uninstall
 */
register_uninstall_hook(__FILE__, array('Breaking_News', 'on_uninstall'));

require_once('class.breaking-news.php');

/**
 * Constructor for the breaking news class. Loads hooks in the init method.
 */
add_action( 'init', array( 'Breaking_News', 'init' ) );

if ( is_admin() ) {
	require_once('class.breaking-news-admin.php');
	
	/**
	 * Constructor for the breaking news admin class. Loads options and hooks in the init method.
	 */
	add_action( 'init', array( 'Breaking_News_Admin', 'init' ) );
	
	/**
	 * Create a link on the plugins page to the breaking news settings page
	 */
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array('Breaking_News_Admin', 'bn_add_action_links'));
}