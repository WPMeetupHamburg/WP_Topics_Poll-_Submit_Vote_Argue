<?php
/**
 * Plugin Name:	WP Simple Feedback
 * Plugin URI:	http://feedback.lindewitz.de
 * Description:	This plugin provides a simple feedback possibility for the users
 * Version:		0.1
 * Author:		Inpsyde GmbH
 * Author URI:	http://inpsyde.com
 * Licence:		GPLv3
 * Text Domain:	wp-simple-feedback
 * Domain Path:	/language
 */

if ( ! class_exists( 'WP_Simple_Feedback' ) ) {
	
	if ( function_exists( 'add_filter' ) )
		add_filter( 'plugins_loaded' ,  array( 'WP_Simple_Feedback', 'get_instance' ) );
	
	class WP_Simple_Feedback {
		
		/**
		 * Instance holder
		 *
		 * @var		NULL | __CLASS__
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @return	__CLASS__
		 */
		public static function get_instance() {
			
			if ( ! self::$instance )
				self::$instance = new self;
			return self::$instance;
		}
		
		/**
		 * Setting up some data, initialize localization and load
		 * the features
		 * 
		 * @return	void
		 */
		public function __construct () {
			
			// Translation
			load_plugin_textdomain( 'wp-simple-feedback', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/language' );
			
			// Load the features
			$this->load_features();
		}
		
		/**
		 * Returns array of features, also
		 * Scans the plugins subfolder "/features"
		 *
		 * @return	void
		 */
		protected function load_features() {
			
			// load all files with the pattern class-*.php from the directory inc
			foreach ( glob( dirname( __FILE__ ) . '/inc/class-*.php' ) as $class )
				require_once $class;
		}
	}
	
	if ( ! function_exists( 'p' ) ) {
		/**
		 * This helper function outputs a given string,
		 * object or array
		 *
		 * @param 	mixed $output
		 * @return	void
		 */
		function p( $output ) {
			print '<br /><br /><br /><pre>';
			print_r( $output );
			print '</pre>';
		}
	}
	
	if ( ! function_exists( 'array_insert' ) ) {
		/**
		 * This little helper function inserts an array to an array
		 * on a specific position
		 *
		 * @param	array $array
		 * @param	string $key
		 * @param	array $insert
		 * @param	boolean $before adds the array before the key
		 * @return	array
		 */
		function array_insert( $array, $key, $insert, $before = FALSE ) {
				
			$index = array_search( $key, array_keys( $array ) );
			if ( $index === FALSE ){
				$index = count( $array );
			} else {
				if ( ! $before )
					$index++;
			}
				
			$end = array_splice( $array, $index );
			return array_merge( $array, $insert, $end );
		}
	}
}