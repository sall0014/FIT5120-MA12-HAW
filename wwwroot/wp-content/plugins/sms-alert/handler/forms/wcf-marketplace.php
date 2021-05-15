<?php
if (! defined( 'ABSPATH' )) exit;
if (!is_plugin_active( 'wc-multivendor-marketplace/wc-multivendor-marketplace.php' ) ) { return; }
class WcfMarketplace extends FormInterface
{  
	private $formSessionVar = FormSessionVars::WCF_DEFAULT_REG;
	
	function handleForm()
	{
		add_action( 'wcfmmp_new_store_created', array( $this, 'wcfm_vendor_created' ), 10, 2 );
	}
	
	public function wcfm_vendor_created($user_id, $data)
	{
		if(array_key_exists('phone', $data))
		{
			do_action('smsalert_after_update_new_user_phone',$user_id,$data['phone']);
		}
	}

	public static function isFormEnabled() 
	{
		return (smsalert_get_option('buyer_signup_otp', 'smsalert_general')=="on") ? true : false;
	}

	function handle_failed_verification($user_login,$user_email,$phone_number)
	{
		SmsAlertUtility::checkSession();
		if(!isset($_SESSION[$this->formSessionVar])) return;
		if(!empty($_REQUEST['option']) && $_REQUEST['option']=='smsalert-validate-otp-form')
		{
			wp_send_json( SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('INVALID_OTP'),'error'));
			exit();
		}
		else
		{
			$_SESSION[$this->formSessionVar] = 'verification_failed';
		}
	}

	function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
	{
		SmsAlertUtility::checkSession();
		if(!isset($_SESSION[$this->formSessionVar])) return;
		if(!empty($_REQUEST['option']) && $_REQUEST['option']=='smsalert-validate-otp-form')
		{
			wp_send_json( SmsAlertUtility::_create_json_response("OTP Validated Successfully.",'success'));
			exit();
		}
		else
		{
			$_SESSION[$this->formSessionVar] = 'validated';
		}
	}

	public function unsetOTPSessionVariables()
	{
		unset($_SESSION[$this->txSessionId]);
		unset($_SESSION[$this->formSessionVar]);
	}

	public function is_ajax_form_in_play($isAjax)
	{
		SmsAlertUtility::checkSession();
		return isset($_SESSION[$this->formSessionVar]) ? true : $isAjax;
	}

	function handleFormOptions()
	{
	}
}
new WcfMarketplace;