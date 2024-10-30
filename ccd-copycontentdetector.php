<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://new-system-create.co.jp
 * @since             1.0.0
 * @package           Ccd_Copycontentdetector
 *
 * @wordpress-plugin
 * Plugin Name:       CopyContentDetector
 * Plugin URI:        https://ccd.cloud
 * Description:       コピペチェックツール「CopyContentDetector」へのリンクを設置することができます。APIと連携するとコピーチェックボタンを設置することができます。
 * Version:           1.1.6
 * Author:            NewSystemCreate
 * Author URI:         https://new-system-create
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ccd-copycontentdetector
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PLUGIN_NAME_VERSION', '1.1.3' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ccd-copycontentdetector-activator.php
 */
function activate_ccd_copycontentdetector() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ccd-copycontentdetector-activator.php';
	Ccd_Copycontentdetector_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ccd-copycontentdetector-deactivator.php
 */
function deactivate_ccd_copycontentdetector() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ccd-copycontentdetector-deactivator.php';
	Ccd_Copycontentdetector_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ccd_copycontentdetector' );
register_deactivation_hook( __FILE__, 'deactivate_ccd_copycontentdetector' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ccd-copycontentdetector.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ccd_copycontentdetector() {

	$plugin = new Ccd_Copycontentdetector();
	$plugin->run();

}
run_ccd_copycontentdetector();
