<?php
/**
#_________________________________________________ PLUGIN
Plugin Name: Timeline Express - Single Column Add-On
Plugin URI: https://www.wp-timelineexpress.com
Description: Defines a new shortcode parameter, single-column="1" (eg: <code>[timeline-express single-column="1"]</code>). The new parameter sets the Timeline Express instance to a single column, similar to the default mobile view.
Version: 1.1.0
Author: Code Parrots
Text Domain: timeline-express-single-column-add-on
Author URI: http://www.codeparrots.com
License: GPL2

#_________________________________________________ LICENSE
Copyright 2012-16 Code Parrots (email : codeparrots@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

#_________________________________________________ CONSTANTS
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {

	die;

}

/**
* Make sure the base is enabled, else kill off
*
* @since 1.0
*/
// must include plugin.php to use is_plugin_active()
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// Define our constants
if ( ! defined( 'TIMELINE_EXPRESS_SINGLE_COLUMN_PATH' ) ) {

	define( 'TIMELINE_EXPRESS_SINGLE_COLUMN_PATH', plugin_dir_path( __FILE__ ) );

}

if ( ! defined( 'TIMELINE_EXPRESS_SINGLE_COLUMN_URL' ) ) {

	define( 'TIMELINE_EXPRESS_SINGLE_COLUMN_URL', plugin_dir_url( __FILE__ ) );

}

/**
*	Admin notice when the base plugin is not installed.
*/
function timeline_express_single_column_addon_display_activation_notice_error() {
	?>
		<!-- hide the 'Plugin Activated' default message -->
		<style>
		#message.updated {
			display: none;
		}
		</style>
		<!-- display our error message -->
		<div class="error">
			<p><?php printf( esc_attr__( '%s could not be activated because Timeline Express is not installed and active.', 'timeline-express-single-column-add-on' ), '<strong>Timeline Express Single Column Add-on</strong>' ); ?></p>
			<p><?php printf( esc_attr__( 'Please install and activate %s before activating this addon.', 'timeline-express-single-column-add-on' ) , '<a href="' . esc_url_raw( admin_url( 'plugin-install.php?tab=search&type=term&s=Timeline+Express+Evan+Herman' ) ) . '" title="Timeline Express">Timeline Express</a>' ); ?></p>
		</div>
	<?php
}
/* end check */

// Hook in and load our class
function initialize_timeline_express_single_column_addon() {

	// ensure that our base plugin is active, else abort & display our notice
	if ( ! class_exists( 'TimelineExpressBase' ) ) {

		deactivate_plugins( plugin_basename( __FILE__ ) );

		add_action( 'admin_notices', 'timeline_express_single_column_addon_display_activation_notice_error' );

		return;

	}

	// Initialize our class
	class Timeline_Express_Single_Column extends TimelineExpressBase {

		// Main Constructor
		public function __construct() {

			include_once( TIMELINE_EXPRESS_SINGLE_COLUMN_PATH . 'lib/class-tinymce.php' );

			// Filter our shortcode to add our new atts
			add_filter( 'shortcode_atts_timeline-express', array( $this, 'add_new_timeline_express_single_column_shortcode_attribute' ), 10, 4 );

		}

		/**
		*	Enable/assign a 'limit' attribute on our shortcode
		*	@since 1.0
		*/
		public function add_new_timeline_express_single_column_shortcode_attribute( $output, $pairs, $atts, $shortcode ) {

			if ( ! isset( $atts['single-column'] ) || 0 === (int) $atts['single-column'] ) {

				return $output;

			}

			$suffix = SCRIPT_DEBUG ? '' : '.min';

			// Enqueue custom styles to style the single column timeline
			wp_enqueue_style( 'timeline-express-single-column-styles', TIMELINE_EXPRESS_SINGLE_COLUMN_URL . "lib/css/timeline-express-single-column{$suffix}.css", array( 'timeline-express-base' ) );

			// Enqueue our scripts to add the appropriate class to our parent timeline container
			wp_enqueue_script( 'timeline-express-single-column-scripts', TIMELINE_EXPRESS_SINGLE_COLUMN_URL . "lib/js/timeline-express-single-column{$suffix}.js", array( 'jquery' ), 'all', true );

			// add our 'single-column' class to the timeline
			add_filter( 'timeline-express-announcement-container-class', array( $this, 'timeline_express_single_column_append_class' ), 11, 2 );

			/* Contaier style overrides */
			add_action( 'timeline-express-container-top', array( $this, 'container_style_overrides' ), PHP_INT_MAX );

			// Enqueue our inline styles, to style the ::before arrow
			self::timeline_express_single_column_enqueue_inline_styles( timeline_express_get_options() );

			// setup the single column shortcode
			$output['single-column'] = $atts['single-column'];

			return $output;

		}

		/**
		 * Append the appropriate class to our timeline containers, for styling purposes
		 *
		 * @param  string  $classes The default classes assigned to the container.
		 * @param  integer $post_id The current post (announcement) ID.
		 *
		 * @return string           The new string of classes for the announcement containers.
		 */
		public function timeline_express_single_column_append_class( $classes, $post_id ) {

			return $classes . ' single-column';

		}

		/**
		 * Load our container style overrides.
		 *
		 * @since 1.0.0
		 *
		 * @return inline styles to override defaults.
		 */
		public function container_style_overrides() {

			if ( ! function_exists( 'timeline_express_styles_metabox_values' ) ) {

				return;

			}

			global $post;

			$metabox_values = timeline_express_styles_metabox_values( $post->ID );

			if ( ! isset( $metabox_values['container_style_styles'] ) || empty( $metabox_values['container_style_styles'] ) ) {

				return;

			}

			$overrides = "
				#cd-timeline .announcement-{$post->ID}.single-column .cd-timeline-content:before {
					border-left-color: transparent !important;
					border-right-color: {$metabox_values['container_style_styles'][ key( $metabox_values['container_style_styles'] ) ]} !important;
				}
			";

			wp_add_inline_style( 'timeline-express-single-column-styles', $overrides );

		}

		/**
		 * Print inline styles so that the single column timeline inherits the appropriate arrow colors from our options
		 * @param  array $options The Timeline Express options array from the database.
		 * @return string         CSS string to print inline for our timeline.
		 */
		public function timeline_express_single_column_enqueue_inline_styles( $options ) {

			$content_background = ( '' === $options['announcement-bg-color'] ) ? 'transparent' : $options['announcement-bg-color'];

			$timeline_express_single_column_styles = '
				.cd-timeline-block.single-column .cd-timeline-content::before {
					border-right-color: ' . $content_background . ' !important;
				}
			';

			wp_add_inline_style( 'timeline-express-single-column-styles', $timeline_express_single_column_styles );

		}

	}

	new Timeline_Express_Single_Column;

}
add_action( 'plugins_loaded', 'initialize_timeline_express_single_column_addon' );
