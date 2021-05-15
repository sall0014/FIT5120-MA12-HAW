<?php
if (! defined( 'ABSPATH' )) exit;
class SAUpgrade
{
	public function __construct() {
		add_action( 'admin_init', array( $this, 'smsalert_upgrade' ), 10 );
	}

	function smsalert_upgrade()
	{
		$db_version = smsalert_get_option( 'version', 'smsalert_upgrade_settings');
		$plugin_version = SmsAlertConstants::SA_VERSION;

		if ( $db_version == $plugin_version ) { //if same go back
			return;
		}

		if($db_version <= '3.4.0') {
			smsalert_WC_Order_SMS::sa_cart_activate();
			//add activation date
			if(!get_option('smsalert_activation_date'))
			{
				add_option('smsalert_activation_date',date('Y-m-d'));
			}
		}
		if($db_version <= '3.3.7.2') {
			$otp_template = smsalert_get_option( 'sms_otp_send', 'smsalert_message');
			if($otp_template == 'Your verification code is [otp]')
			{
				$output = get_option('smsalert_message');
				$output['sms_otp_send'] = 'Your verification code for [shop_url] is [otp]';
				update_option( 'smsalert_message', $output);
			}
		}
		
		update_option( 'smsalert_upgrade_settings', array('version'=>$plugin_version));
	}
}
new SAUpgrade;