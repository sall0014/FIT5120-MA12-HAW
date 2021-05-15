<?php

namespace WPDesk\ShopMagicTwilio;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ShopMagicTwilioVendor\WPDesk\Notice\Notice;
use ShopMagicTwilioVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use ShopMagicTwilioVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use ShopMagicTwilioVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use ShopMagicTwilioVendor\WPDesk_Plugin_Info;
use WPDesk\ShopMagic\Integration\ExternalPluginsAccess;

/**
 * Main plugin class. The most important flow decisions are made here.
 */
class Plugin extends AbstractPlugin implements LoggerAwareInterface, HookableCollection {
	use LoggerAwareTrait;
	use HookableParent;

	/**
	 * Plugin constructor.
	 *
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( WPDesk_Plugin_Info $plugin_info ) {
		/** @noinspection PhpParamsInspection */
		parent::__construct( $plugin_info );
		$this->setLogger( new NullLogger() );

		$this->docs_url    = 'https://docs.shopmagic.app/';
		$this->support_url = 'https://shopmagic.app/support/';
	}

	/**
	 * @inheritDoc
	 */
	public function hooks() {
		parent::hooks();

		add_action( 'shopmagic/core/initialized/v2', function ( ExternalPluginsAccess $external_plugins_access ) {
			$shopmagic_version = $external_plugins_access->get_version();
			if ( version_compare( $shopmagic_version, '3', '>=' ) ) {
				new \ShopMagicTwilioVendor\WPDesk\Notice\Notice( __( "This version of ShopMagic for Twilio plugin is not compatible with ShopMagic {$shopmagic_version}. Please upgrade ShopMagic Manual Actions to the newest version.",
					'shopmagic-abandoned-carts' ) );

				return;
			}
			if ( version_compare( $shopmagic_version, '2.17.0', '<' ) ) {
				new Notice( __( "This version of ShopMagic for Twilio plugin is not compatible with ShopMagic {$shopmagic_version}. Please upgrade ShopMagic to the newest version.",
					'shopmagic-abandoned-carts' ) );

				return;
			}

			$this->logger = $external_plugins_access->get_logger();

			$settings = new Admin\Settings();

			if ( is_admin() ) {

				add_filter( 'shopmagic/core/settings/tabs', function ( $settings_tab ) use ( $settings ) {
					$settings_tab[ Admin\Settings::get_tab_slug() ] = $settings;

					return $settings_tab;
				} );
			}
			add_filter( 'shopmagic/core/actions', function ( $actions ) use ( $settings ) {
				$new_actions = [
					'twilio_send_sms' => new Action\TwilioSendSms( $settings )
				];

				return array_merge( $actions, $new_actions );
			} );
		}, 10, 3 );
	}
}
