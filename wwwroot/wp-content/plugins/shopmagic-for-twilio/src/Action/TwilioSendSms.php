<?php

namespace WPDesk\ShopMagicTwilio\Action;

use WPDesk\ShopMagic\Action\BasicAction;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Event\Event;
use WPDesk\ShopMagic\FormField\Field\InputTextField;
use WPDesk\ShopMagic\FormField\Field\TextAreaField;
use WPDesk\ShopMagicTwilio\Admin\Settings;


final class TwilioSendSms extends BasicAction {
	const PARAM_NAME_MESSAGE = 'message';
	const PARAM_NAME_FROM = 'target_from';
	const PARAM_NAME_TO = 'target_phone';

	private $account_sid;
	private $auth_token;
	private $phone_number;

	public function __construct( Settings $settings ) {
		$this->account_sid  = trim( $settings::get_option( Settings::PARAM_NAME_SSID ) );
		$this->auth_token   = trim( $settings::get_option( Settings::PARAM_NAME_TOKEN ) );
		$this->phone_number = trim( $settings::get_option( Settings::PARAM_NAME_PHONE ) );
	}

	public function get_name(): string {
		return __( 'Send SMS with Twilio', 'shopmagic-for-twilio' );
	}

	public function get_fields(): array {
		return [
			( new InputTextField() )
				->set_label( __( 'To', 'shopmagic-for-twilio' ) )
				->set_name( self::PARAM_NAME_TO )
				->set_description( __( 'Country code + 10-digit phone number (i.e. +16592045629)', 'shopmagic-for-twilio' ) )
				->set_default_value( '{{ customer.phone }} ' ),
			( new InputTextField() )
				->set_label( __( 'From', 'shopmagic-for-twilio' ) )
				->set_description( __( 'Country code + 10-digit Twilio phone number (i.e. +16592045629)', 'shopmagic-for-twilio' ) )
				->set_default_value( $this->phone_number )
				->set_name( self::PARAM_NAME_FROM ),
			( new TextAreaField() )
				->set_label( __( 'Message', 'shopmagic-for-woocommerce' ) )
				->set_name( self::PARAM_NAME_MESSAGE )
		];
	}

	public function execute( Automation $automation, Event $event ): bool {
		$to = $this->fields_data->get( self::PARAM_NAME_TO );
		if ( empty( $to ) ) {
			$to = '{{ customer.phone }}';
		}
		$args = [
			'headers'   => [
				'Authorization' => 'Basic ' . base64_encode( $this->account_sid . ':' . $this->auth_token ),
				'Accept'        => 'application/json'
			],
			'body'      => http_build_query( [
				'To'   => $this->placeholder_processor->process( $to ),
				'From' => $this->placeholder_processor->process( $this->fields_data->get( self::PARAM_NAME_FROM ) ),
				'Body' => $this->placeholder_processor->process( $this->fields_data->get( self::PARAM_NAME_MESSAGE ) )
			] ),
			'timeout'   => 15,
			'method'    => 'POST',
			'sslverify' => false,
		];

		$args =
			/**
			 * Request arguments to Twilio API. You can easily edit all SMS data.
			 * Especially:
			 *  $args['body']['To'] - receiver phone number.
			 *  $args['body']['From'] - sender phone number.
			 *  $args['body']['Body'] - message content.
			 *
			 * @param array $args wp_remote_post arguments.
			 * @param Event $event Event that was reason to fire the action.
			 * @param Automation $automation Automation that runs the action.
			 *
			 * @return array wp_remote_post arguments.
			 *
			 * @see https://developer.wordpress.org/reference/functions/wp_remote_post/
			 * @since 1.0
			 */
			apply_filters( 'shopmagic/twilio/send_sms/remote_post_args', $args, $event, $automation );

		$response = wp_remote_post( "https://api.twilio.com/2010-04-01/Accounts/{$this->account_sid}/Messages.json", $args );

		$success_codes = [ 200, 201, 202, 203, 204 ];
		$error         = $response instanceof \WP_Error || ( is_array( $response ) && ! in_array( $response['response']['code'] ?? '', $success_codes, true ) );

		if ( $error ) {
			$this->logger->error( 'Has some problems with sending a sms', [ 'response' => $response ] );
		}

		return ! $error;
	}

	public function get_required_data_domains(): array {
		return [];
	}
}
