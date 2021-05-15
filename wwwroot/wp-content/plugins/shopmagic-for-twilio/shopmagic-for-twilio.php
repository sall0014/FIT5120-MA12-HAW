<?php
/*
Plugin Name: ShopMagic for Twilio
Plugin URI: https://shopmagic.app/products/woocommerce-subscriptions/
Description: Allows users to send SMS using Twilio account.
Version: 1.0.1
Author: WP Desk
Author URI: https://shopmagic.app/
Text Domain: shopmagic-for-twilio
Domain Path: /lang/
Requires at least: 4.9
Tested up to: 5.7
WC requires at least: 4.5
WC tested up to: 5.2
Requires PHP: 7.0

Copyright 2020 WP Desk Ltd.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


/* THESE TWO VARIABLES CAN BE CHANGED AUTOMATICALLY */
$plugin_version           = '1.0.1';

$plugin_name        = 'ShopMagic for Twilio';
$plugin_class_name  = '\WPDesk\ShopMagicTwilio\Plugin';
$plugin_text_domain = 'shopmagic-for-twilio';
$product_id         = 'ShopMagic for Twilio';
$plugin_file        = __FILE__;
$plugin_dir         = dirname( __FILE__ );

$requirements = [
	'php'     => '7.0',
	'wp'      => '4.9.15',
	'plugins' => [
		[
			'name'      => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
		],
		[
			'name'      => 'shopmagic-for-woocommerce/shopMagic.php',
			'nice_name' => 'ShopMagic for WooCommerce',
			'version'   => '2.17.0'
		]
	],

];

if ( \PHP_VERSION_ID > 50300 ) {
	require_once $plugin_dir . '/vendor/autoload.php';

	$requirements_checker = ( new \ShopMagicTwilioVendor\WPDesk_Basic_Requirement_Checker_Factory() )
		->create_from_requirement_array(
			__FILE__,
			$plugin_name,
			$requirements,
			$plugin_text_domain
		);

	$plugin_info = new \ShopMagicTwilioVendor\WPDesk_Plugin_Info();
	$plugin_info->set_plugin_file_name( plugin_basename( $plugin_file ) );
	$plugin_info->set_plugin_name( $plugin_name );
	$plugin_info->set_plugin_dir( $plugin_dir );
	$plugin_info->set_class_name( $plugin_class_name );
	$plugin_info->set_version( $plugin_version );
	$plugin_info->set_product_id( $product_id );
	$plugin_info->set_text_domain( $plugin_text_domain );
	$plugin_info->set_plugin_url( plugins_url( dirname( plugin_basename( $plugin_file ) ) ) );

	add_action(
		'plugins_loaded',
		static function () use ( $requirements_checker, $plugin_info, $plugin_class_name ) {
			if ( $requirements_checker->are_requirements_met() ) {
				$plugin = new $plugin_class_name( $plugin_info );
				$plugin->init();
			} else {
				$requirements_checker->render_notices();
			}
		},
		- 50
	);
}
