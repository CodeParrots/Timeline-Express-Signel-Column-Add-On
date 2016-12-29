<?php
/**
#_________________________________________________ PLUGIN
Plugin Name: Timeline Express - Single Column Add-On
Plugin URI: https://www.wp-timelineexpress.com
Description: Defines a new shortcode parameter, single-column="1" (eg: <code>[timeline-express single-column="1"]</code>). The new parameter sets the Timeline Express instance to a single column, similar to the default mobile view.
Version: 1.0.4
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
*	Make sure the base is enabled, else kill off
*	@since 1.0
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
			// Add our custom section to the shortcode generator
			add_action( 'timeline-express-shortcode-generator-sections', array( $this, 'generate_single_column_section_shortcode_generator' ) );
			// Filter our shortcode to add our new atts
			add_filter( 'shortcode_atts_timeline-express', array( $this, 'add_new_timeline_express_single_column_shortcode_attribute' ), 10, 4 );
		}

		/**
		*	Generate our custom 'limits' section in the shortcode generator modal window
		*	@since 1.0
		*/
		public function generate_single_column_section_shortcode_generator() {
			include_once( TIMELINE_EXPRESS_SINGLE_COLUMN_PATH . 'lib/templates/single-column-shortcode-generator-section.php' );
		}

		/**
		*	Enable/assign a 'limit' attribute on our shortcode
		*	@since 1.0
		*/
		public function add_new_timeline_express_single_column_shortcode_attribute( $output, $pairs, $atts, $shortcode ) {
			if ( isset( $atts['single-column'] ) && 0 !== $atts['single-column'] ) {
				// Enqueue custom styles to style the single column timeline
				wp_enqueue_style( 'timeline-express-single-column-styles', TIMELINE_EXPRESS_SINGLE_COLUMN_URL . 'lib/css/min/timeline-express-single-column.min.css', array( 'timeline-express-base' ) );
				// Enqueue our scripts to add the appropriate class to our parent timeline container
				wp_enqueue_script( 'timeline-express-single-column-scripts', TIMELINE_EXPRESS_SINGLE_COLUMN_URL . 'lib/js/min/timeline-express-single-column.min.js', array( 'jquery' ), 'all', true );
				// add our 'single-column' class to the timeline
				add_filter( 'timeline-express-announcement-container-class', array( $this, 'timeline_express_single_column_append_class' ), 11, 2 );
				// Enqueue our inline styles, to style the ::before arrow
				self::timeline_express_single_column_enqueue_inline_styles( timeline_express_get_options() );
				// Re-arrange our containers, for the new layout
				self::timeline_Express_single_column_adjust_layout();
				// setup the single column shortcode
				$output['single-column'] = $atts['single-column'];
			}
			return $output;
		}

		/**
		 * Append the appropriate class to our timeline containers, for styling purposes
		 * @param  string  $classes The default classes assigned to the container.
		 * @param  integer $post_id The current post (announcement) ID.
		 * @return string						The new string of classes for the announcement containers.
		 */
		public function timeline_express_single_column_append_class( $classes, $post_id ) {
			return $classes . ' single-column';
		}

		/**
		 * Print inline styles so that the single column timeline inherits the appropriate arrow colors from our options
		 * @param  array $options The Timeline Express options array from the database.
		 * @return string         CSS string to print inline for our timeline.
		 */
		public function timeline_express_single_column_enqueue_inline_styles( $options ) {
			$content_background = ( '' === $options['announcement-bg-color'] ) ? 'transparent' : $options['announcement-bg-color'];
			$timeline_express_single_column_styles = '.cd-timeline-block.single-column .cd-timeline-content::before {
				border-right-color: ' . $content_background . ' !important;
			}';
			wp_add_inline_style( 'timeline-express-single-column-styles', $timeline_express_single_column_styles );
		}

		/**
		 * Alter the layout of the single column timelines
		 * This will move the announcement image inside the excerpt, for a new layout.
		 * @return null
		 * @since 1.0
		 */
		public function timeline_Express_single_column_adjust_layout() {
			// append the image into the excerpt
			add_filter( 'timeline_express_frontend_excerpt', array( $this, 'append_image_inside_timeline_excerpt' ), 10, 2 );
		}

		/**
		 * Move the image inside of the excerpt
		 * @param  string  $excerpt The default excerpt for the announcement.
		 * @param  integer $post_id The current announcement ID in the loop.
		 * @return string           The new announcement image to be used.
		 * @since 1.0
		 */
		public function append_image_inside_timeline_excerpt( $excerpt, $post_id ) {
			return timeline_express_get_announcement_image( $post_id ) . $excerpt;
		}
	}
	new Timeline_Express_Single_Column;
}
add_action( 'plugins_loaded', 'initialize_timeline_express_single_column_addon' );
/* End */
