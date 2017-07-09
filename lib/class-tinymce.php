<?php
/**
 * Timeline Express Single Column TinyMCE shortcode generator fields
 *
 * @since 2.0.0
 */
final class Timeline_Express_Single_Column_TinyMCE {

	public function __construct() {

		add_action( 'init', [ $this, 'generate_fields' ] );

	}

	/**
	 * Add new fields to the shortcode generator.
	 *
	 * @since 2.0.0
	 */
	public function generate_fields() {

		if ( ! function_exists( 'timeline_express_shortcode_generator_field' ) ) {

			return;

		}

		$single_column_fields = [
			[
				'attribute' => 'single-column', // used by our helper function only.
				'type'      => 'checkbox',
				'classes'   => 'single-column',
				'label'     => esc_html__( 'Single Column', 'timeline-express-pro' ),
				'style'     => 'height: 40px;',
				'tooltip'   => esc_html__( 'Display this timeline in a single column.', 'timeline-express-pro' ),
			],
		];

		timeline_express_shortcode_generator_field( $single_column_fields );

	}

}

new Timeline_Express_Single_Column_TinyMCE;
