<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://new-system-create.co.jp
 * @since      1.0.0
 *
 * @package    Ccd_Copycontentdetector
 * @subpackage Ccd_Copycontentdetector/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Ccd_Copycontentdetector
 * @subpackage Ccd_Copycontentdetector/includes
 * @author     Sumito Umeda <umeda@new-system-create.co.jp>
 */
class Ccd_Copycontentdetector_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ccd-copycontentdetector',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
