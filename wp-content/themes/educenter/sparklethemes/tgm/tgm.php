<?php
/**
 * Recommended plugins
 *
 * @package Educenter
 */

if ( ! function_exists( 'educenter_recommended_plugins' ) ) :

	/**
	 * Recommend plugins.
	 *
	 * @since 1.0.0
	 */
	function educenter_recommended_plugins() {

		$plugins = array(
			
			array(
				'name'     => esc_html__( 'Contact Form 7', 'educenter' ),
				'slug'     => 'contact-form-7',
				'required' => false,
			),

			array(
				'name'     => esc_html__( 'WooCommerce', 'educenter' ),
				'slug'     => 'woocommerce',
				'required' => false,
			),
			
			array(
				'name'     => esc_html__( 'Page Builder by SiteOrigin', 'educenter' ),
				'slug'     => 'siteorigin-panels',
				'required' => false,
			),

			array(
				'name'     => esc_html__( 'Polylang', 'educenter' ),
				'slug'     => 'polylang',
				'required' => false,
			),

			array(
				'name'     => esc_html__( 'Elementor Page Builder', 'educenter' ),
				'slug'     => 'elementor',
				'required' => false,
			),

			array(
				'name'     => esc_html__( 'Loco Translate', 'educenter' ),
				'slug'     => 'loco-translate',
				'required' => false,
			),
			array(
				'name'     => esc_html__( 'LearnPress LMS', 'educenter' ),
				'slug'     => 'learnpress',
				'required' => false,
			)
		);

		tgmpa( $plugins );

	}

endif;

add_action( 'tgmpa_register', 'educenter_recommended_plugins' );
