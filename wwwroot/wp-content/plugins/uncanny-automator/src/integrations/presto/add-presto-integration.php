<?php

namespace Uncanny_Automator;

/**
 * Class Add_Presto_Integration
 * @package Uncanny_Automator
 */
class Add_Presto_Integration {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'PRESTO';

	/**
	 * Add_Integration constructor.
	 */
	public function __construct() {

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
			if ( defined( 'PRESTO_PLAYER_PLUGIN_FILE' ) ) {
				$status = true;
			} else {
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * et the directories that the auto loader will run in
	 *
	 * @param $directory
	 *
	 * @return array
	 */
	public function add_integration_directory_func( $directory ) {

		$directory[] = dirname( __FILE__ ) . '/helpers';
		$directory[] = dirname( __FILE__ ) . '/triggers';
		$directory[] = dirname( __FILE__ ) . '/tokens';

		return $directory;
	}

	/**
	 * Register the integration by pushing it into the global automator object
	 */
	public function add_integration_func() {

		global $uncanny_automator;

		$uncanny_automator->register->integration(
			self::$integration,
			array(
				'name'     => 'Presto',
				'icon_svg' => Utilities::get_integration_icon( 'presto-player-icon.svg' ),
			)
		);
	}
}
