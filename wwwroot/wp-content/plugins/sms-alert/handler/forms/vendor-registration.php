<?php
if (! defined( 'ABSPATH' )) exit;
if (!is_plugin_active( 'woocommerce-product-vendors/woocommerce-product-vendors.php')){return;}
class VendorRegistrationForm extends FormInterface
{
	public static $response_array 	= array();
	private $formSessionVar 		= FormSessionVars::PV_DEFAULT_REG;
	private $phoneFormID 			= "input[name^='billing_phone']";

	function handleForm()
	{
		add_action( 'wcpv_shortcode_registration_form_process', array( $this, 'smsalert_pv_registration_complete' ), 10, 2 );
		add_action( 'wcpv_registration_form',	array($this,'vendors_reg_custom_fields') );
	}

	public static function vendors_reg_custom_fields()
	{
		echo '<p class="form-row form-row-wide">
			  <label for="wcpv-vendor-billing-phone">Phone <span class="required">*</span></label>
			  <input class="input-text" type="text" name="billing_phone" id="wcpv-billing-phone" value="" tabindex="6">
			  </p>';
		echo do_shortcode('[sa_verify id="form1" phone_selector="#wcpv-billing-phone" submit_selector= "register" ]');
	}

	function smsalert_pv_registration_complete($args, $items)
	{
		$data = get_user_by('login', $items['username']);
		if ( isset( $items['billing_phone'] )) {
			add_user_meta( $data->ID, 'billing_phone', $items['billing_phone'],true);
		}
		do_action('smsalert_after_update_new_user_phone', $data->ID, $items['billing_phone']);
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

	public function getPhoneNumberSelector($selector)
	{
		SmsAlertUtility::checkSession();
		if(self::isFormEnabled()) array_push($selector, $this->phoneFormID);
		return $selector;
	}

	function handleFormOptions()
	{
	}
}
new VendorRegistrationForm;
?>