<?php


namespace Uncanny_Automator;

use Uncanny_Automator_Pro\Gamipress_Pro_Helpers;

/**
 * Class Gamipress_Helpers
 * @package Uncanny_Automator
 */
class Gamipress_Helpers {
	/**
	 * @var Gamipress_Helpers
	 */
	public $options;
	/**
	 * @var Gamipress_Pro_Helpers
	 */
	public $pro;
	/**
	 * @var bool
	 */
	public $load_options;

	/**
	 * Gamipress_Helpers constructor.
	 */
	public function __construct() {
		global $uncanny_automator;
		$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );

		add_action( 'wp_ajax_select_achievements_from_types_AWARDACHIEVEMENT', [
			$this,
			'select_achievements_from_types_func'
		] );
		add_action( 'wp_ajax_select_ranks_from_types_AWARDRANKS', [ $this, 'select_ranks_from_types_func' ] );
	}

	/**
	 * @param Gamipress_Helpers $options
	 */
	public function setOptions( Gamipress_Helpers $options ) {
		$this->options = $options;
	}

	/**
	 * @param Gamipress_Pro_Helpers $pro
	 */
	public function setPro( Gamipress_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function list_gp_award_types( $label = null, $option_code = 'GPAWARDTYPES', $args = [] ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label =  esc_attr__( 'Achievement type', 'uncanny-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$options      = [];

		global $uncanny_automator, $wpdb;
		if ( $uncanny_automator->helpers->recipe->load_helpers ) {

			//$posts = $uncanny_automator->helpers->recipe->options->wp_query( [ 'post_type' => 'achievement-type' ] );
			$posts = $wpdb->get_results( "SELECT ID, post_name, post_title, post_type 
											FROM $wpdb->posts 
											WHERE post_type LIKE 'achievement-type' AND post_status = 'publish' ORDER BY post_title ASC" );
			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$options[ $post->post_name ] = $post->post_title;
				}
			}
			/* translators: GamiPress achievement type */
			$options['points-award'] =  esc_attr__( 'Points awards', 'uncanny-automator' );
			/* translators: GamiPress achievement type */
			$options['step'] =  esc_attr__( 'Step', 'uncanny-automator' );
			/* translators: GamiPress achievement type */
			$options['rank-requirement'] =  esc_attr__( 'Rank requirement', 'uncanny-automator' );
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
			'custom_value_description' => _x( 'Achievement type slug', 'GamiPress', 'uncanny-automator' ),
		];

		return apply_filters( 'uap_option_list_gp_award_types', $option );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function list_gp_points_types( $label = null, $option_code = 'GPPOINTSTYPES', $args = [] ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label =  esc_attr__( 'Point type', 'uncanny-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$include_all  = key_exists( 'include_all', $args ) ? $args['include_all'] : false;

		$options = [];

		if ( $include_all ) {
			$options['ua-all-gp-types'] =  esc_attr__( 'All point types', 'uncanny-automator' );
		}

		global $uncanny_automator, $wpdb;
		if ( $uncanny_automator->helpers->recipe->load_helpers ) {

			//$posts = $uncanny_automator->helpers->recipe->options->wp_query( [ 'post_type' => 'points-type' ] );
			$posts = $wpdb->get_results( "SELECT ID, post_name, post_title, post_type
											FROM $wpdb->posts 
											WHERE post_type LIKE 'points-type' AND post_status = 'publish' ORDER BY post_title ASC" );
			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$options[ $post->post_name ] = $post->post_title;
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
			'custom_value_description' => _x( 'Point type slug', 'GamiPress', 'uncanny-automator' ),
		];

		return apply_filters( 'uap_option_list_gp_points_types', $option );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function list_gp_rank_types( $label = null, $option_code = 'GPRANKTYPES', $args = [] ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label =  esc_attr__( 'Rank type', 'uncanny-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$options      = [];

		global $uncanny_automator, $wpdb;
		if ( $uncanny_automator->helpers->recipe->load_helpers ) {

			//$posts = $uncanny_automator->helpers->recipe->options->wp_query( [ 'post_type' => 'rank-type' ] );
			$posts = $wpdb->get_results( "SELECT ID, post_name, post_title, post_type 
											FROM $wpdb->posts 
											WHERE post_type LIKE 'rank-type' AND post_status = 'publish' ORDER BY post_title ASC" );
			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$options[ $post->post_name ] = $post->post_title;
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
			'custom_value_description' => _x( 'Rank type slug', 'GamiPress', 'uncanny-automator' ),
		];

		return apply_filters( 'uap_option_list_gp_rank_types', $option );
	}

	/**
	 * Return all the specific fields of a form ID provided in ajax call
	 */
	public function select_achievements_from_types_func() {

		global $uncanny_automator;

		// Nonce and post object validation
		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = [];
		if ( isset( $_POST['value'] ) && ! empty( $_POST['value'] ) ) {

			$args = [
				'post_type'      => sanitize_text_field( $_POST['value'] ),
				'posts_per_page' => 999,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			];

			$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, false,  esc_attr__( 'Any awards', 'uncanny-automator' ) );

			foreach ( $options as $award_id => $award_name ) {
				$fields[] = [
					'value' => $award_id,
					'text'  => $award_name,
				];
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * Return all the specific fields of a form ID provided in ajax call
	 */
	public function select_ranks_from_types_func() {

		global $uncanny_automator;

		// Nonce and post object validation.
		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = [];
		if ( isset( $_POST['value'] ) && ! empty( $_POST['value'] ) ) {

			$args = [
				'post_type'      => sanitize_text_field( $_POST['value'] ),
				'posts_per_page' => 999,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			];

			$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, false,  esc_attr__( 'Any awards', 'uncanny-automator' ) );

			foreach ( $options as $award_id => $award_name ) {
				$fields[] = [
					'value' => $award_id,
					'text'  => $award_name,
				];
			}
		}
		echo wp_json_encode( $fields );
		die();
	}
}