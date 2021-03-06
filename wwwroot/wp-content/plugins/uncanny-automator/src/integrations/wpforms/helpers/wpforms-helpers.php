<?php


namespace Uncanny_Automator;


use Uncanny_Automator_Pro\Wpforms_Pro_Helpers;
use WPForms_Form_Handler;

/**
 * Class Wpforms_Helpers
 * @package Uncanny_Automator
 */
class Wpforms_Helpers {
	/**
	 * @var Wpforms_Helpers
	 */
	public $options;

	/**
	 * @var Wpforms_Pro_Helpers
	 */
	public $pro;

	/**
	 * @var bool
	 */
	public $load_options;

	/**
	 * Wpforms_Helpers constructor.
	 */
	public function __construct() {
		global $uncanny_automator;
		$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
	}

	/**
	 * @param Wpforms_Helpers $options
	 */
	public function setOptions( Wpforms_Helpers $options ) {
		$this->options = $options;
	}

	/**
	 * @param Wpforms_Pro_Helpers $pro
	 */
	public function setPro( Wpforms_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed
	 */

	public function list_wp_forms( $label = null, $option_code = 'WPFFORMS', $args = [] ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		global $uncanny_automator;
		if ( ! $label ) {
			$label =  esc_attr__( 'Form', 'uncanny-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$options      = [];

		if ( $uncanny_automator->helpers->recipe->load_helpers ) {
			$wpforms = new WPForms_Form_Handler();

			$forms = $wpforms->get( '', [
				'orderby' => 'title',
			] );

			if ( ! empty( $forms ) ) {
				foreach ( $forms as $form ) {
					$options[ $form->ID ] = esc_html( $form->post_title );
				}
			}
		}
		$type = 'select';

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => $type,
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
		];

		return apply_filters( 'uap_option_list_wp_forms', $option );
	}
}