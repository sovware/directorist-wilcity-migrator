<?php
/**
 * Wilcity_To_Directorist_Migrator
 *
 * @package           Wilcity_To_Directorist_Migrator
 * @author            wpWax
 * @copyright         2022 wpWax
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Wilcity to Directorist Migrator
 * Plugin URI:        https://wordpress.org/plugins/wilcity-to-directorist-migrator/
 * Description:       Wilcity to Directorist Migrator is a migrator plugin that moves your listings data from Wilcity Business Directory to Directorist.
 * Version:           0.1.2
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            wpWax
 * Author URI:        https://github.com/sovware
 * Text Domain:       wilcity-to-directorist-migrator
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

require dirname( __FILE__ ) . '/vendor/autoload.php';
require dirname( __FILE__ ) . '/app.php';

if ( ! function_exists( 'Wilcity_To_Directorist_Migrator' ) ) {
    // die(class_exists('Wilcity_To_Directorist_Migrator'));
    function Wilcity_To_Directorist_Migrator() {

        return Wilcity_To_Directorist_Migrator::get_instance();
    }
}

// Wilcity_To_Directorist_Migrator();

add_action( 'plugins_loaded', 'Wilcity_To_Directorist_Migrator', 11 );