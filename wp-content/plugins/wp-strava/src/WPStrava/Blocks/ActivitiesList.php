<?php
/*
 * ActivitiesList block.
 */

class WPStrava_Blocks_ActivitiesList implements WPStrava_Blocks_Interface {

	/**
	 * Whether or not to enqueue styles (if shortcode is present).
	 *
	 * @var boolean
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.5.0
	 */
	private $add_script = false;

	/**
	 * Register the wp-strava/activitieslist block.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.2.0
	 */
	public function register_block() {
		register_block_type(
			'wp-strava/activitieslist',
			array(
				'style'           => 'wp-strava-block',
				'editor_style'    => 'wp-strava-block-editor',
				'editor_script'   => 'wp-strava-block',
				'render_callback' => array( $this, 'render_block' ),
				'attributes'      => array(
					'som' => array(
						'type'    => 'string',
						'default' => null,
					),
				),
			)
		);
		add_action( 'wp_footer', array( $this, 'print_scripts' ) );
	}

	/**
	 * Render for this block.
	 *
	 * @param array $attributes JSON attributes saved in the HTML comment for this block.
	 * @param string $content The content from JS save() for this block.
	 * @return string HTML for this block.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.2.0
	 */
	public function render_block( $attributes, $content ) {
		$this->add_script = true;

		// Transform from block attributes to shortcode standard.
		$attributes = array(
			'som' => ! empty( $attributes['som'] ) ? $attributes['som'] : null,
		);

		$renderer = new WPStrava_ActivitiesListRenderer();
		return $renderer->get_html( $attributes );
	}

	/**
	 * Enqueue style if block is being used.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.5.0
	 */
	public function print_scripts() {
		if ( $this->add_script ) {
			wp_enqueue_style( 'wp-strava-style' );
		}
	}
}
