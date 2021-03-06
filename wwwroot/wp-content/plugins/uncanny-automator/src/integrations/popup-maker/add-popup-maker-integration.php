<?php

namespace Uncanny_Automator;

/**
 * Class Add_Wp_Integration
 * @package Uncanny_Automator
 */
class Add_Popup_Maker_Integration {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'PM';

	/**
	 * Add_Integration constructor.
	 */
	public function __construct() {

		// Add directories to auto loader
		// add_filter( 'uncanny_automator_integration_directory', [ $this, 'add_integration_directory_func' ], 11 );

		// Add code, name and icon set to automator
		// add_action( 'uncanny_automator_add_integration', [ $this, 'add_integration_func' ] );

		// Verify is the plugin is active based on integration code
//		add_filter( 'uncanny_automator_maybe_add_integration', [
//			$this,
//			'plugin_active',
//		], 30, 2 );

		// filter Popup Maker triggers
		add_filter( 'pum_registered_triggers', [ $this, 'uap_add_new_popup_trigger' ] );

		add_filter( 'pum_popup_is_loadable', [ $this, 'maybe_disable_pop_up' ], 10, 2 );
	}

	/**
	 * Only load this integration and its triggers and actions if the related plugin is active
	 *
	 * @param $status
	 * @param $plugin
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $plugin ) {

		if ( self::$integration === $plugin ) {
			if ( class_exists( 'Popup_Maker' ) ) {
				$status = true;
			} else {
				$status = false;
			}
		}

		return $status;
	}


	/**
	 * Set the directories that the auto loader will run in
	 *
	 * @param $directory
	 *
	 * @return array
	 */
	public function add_integration_directory_func( $directory ) {

		$directory[] = dirname( __FILE__ ) . '/actions';
		$directory[] = dirname( __FILE__ ) . '/triggers';
		$directory[] = dirname( __FILE__ ) . '/tokens';

		return $directory;
	}

	/**
	 * Register the integration by pushing it into the global automator object
	 */
	public function add_integration_func() {

		global $uncanny_automator;

		$uncanny_automator->register->integration( self::$integration, array(
			'name'     => 'Popup Maker',
			'icon_svg' => Utilities::get_integration_icon( 'popup-maker-icon.svg' ),
		) );
	}

	/**
	 * Add a Automator trigger type to Popup Maker
	 *
	 * @param $triggers
	 *
	 * @return array
	 */
	public function uap_add_new_popup_trigger( $triggers ) {

		$triggers['automator'] = array(
			/* translators: 1. Trademarked term */
			'name'            => sprintf(  esc_attr__( '%1$s recipe is completed', 'uncanny-automator' ), 'Automator' ),
			'modal_title'     =>  esc_attr__( 'Settings', 'uncanny-automator' ),
			'settings_column' => sprintf( '<strong>%1$s</strong>: %2$s',  esc_attr__( 'Recipes', 'uncanny-automator' ), '{{data.recipe}}' ),
			'fields'          => array(
				'general' => array(
					'recipe' => array(
						'label'     =>  esc_attr__( 'Recipe', 'uncanny-automator' ),
						'type'      => 'postselect',
						'post_type' => 'uo-recipe',
						'multiple'  => true,
						'as_array'  => true,
						'std'       => array(),
					),
				),
			),
		);

		return $triggers;
	}

	/**
	 * Disable the popup if the trigger is Automator and the action has not been completed
	 *
	 * @param $loadable
	 * @param $pop_id
	 *
	 * @return bool
	 */
	public function maybe_disable_pop_up( $loadable, $pop_id ) {

		global $wpdb;

		$popup_settings = $wpdb->get_results( "SELECT post_id, meta_value as settings FROM $wpdb->postmeta WHERE meta_key = 'popup_settings'" );

		// All recipes that have popup maker triggers
		$recipes_enabled_in_popups = [];

		foreach ( $popup_settings as $popup ) {

			$popup_id       = $popup->post_id;
			$popup_settings = maybe_unserialize( $popup->settings );

			if ( isset( $popup_settings['triggers'] ) ) {
				foreach ( $popup_settings['triggers'] as $trigger ) {
					if ( 'automator' === $trigger['type'] ) {
						if ( isset( $trigger['settings'] ) && isset( $trigger['settings']['recipe'] ) ) {
							foreach ( $trigger['settings']['recipe'] as $recipe_id ) {
								$recipes_enabled_in_popups[ $popup_id ][] = absint( $recipe_id );
							}
						}
					}
				}
			}
		}

		global $wpdb;
		$automator_popups = $wpdb->get_col(
			"SELECT post_parent FROM $wpdb->posts WHERE ID in (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'POPUPID')"
		);

		// Is the pop up restricted by automator action completion
		if ( isset( $recipes_enabled_in_popups[ $pop_id ] ) && ! empty( array_intersect( $recipes_enabled_in_popups[ $pop_id ], $automator_popups ) ) ) {

			$is_action_popup_ids_enabled = get_user_meta( get_current_user_id(), 'display_pop_up_' . $pop_id, false );

			// if an this action was competed then a meta value was stores for this pop up
			if ( is_array( $is_action_popup_ids_enabled ) && in_array( (string) $pop_id, $is_action_popup_ids_enabled ) ) {
				return true;
			} else {
				return false;
			}
		}

		return $loadable;
	}


}
