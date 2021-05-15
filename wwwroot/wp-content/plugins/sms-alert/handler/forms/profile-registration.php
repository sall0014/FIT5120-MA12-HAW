<?php
if (! defined( 'ABSPATH' )) exit;
if (!is_plugin_active( 'profile-builder/index.php' ) ) { return; }
class ProfileRegistrationForm extends FormInterface
{
	public static $response_array 	= array();
	private $formSessionVar 		= FormSessionVars::PR_DEFAULT_REG;
	private $phoneFormID 			= "input[name^='billing_phone']";

	function handleForm()
	{
		add_action( 'wppb_register_success', array( $this, 'smsalert_pb_registration_complete' ), 20, 3 );
		add_action( 'wppb_after_form_fields', array( $this, 'wppb_phone_add_form_form_builder' ), 10, 2 );
	}

	public static function wppb_phone_add_form_form_builder($output, $form_location = '')
	{
		if($form_location=='register')
		{
			echo '<li class="wppb-form-field wppb-default-phone" id="wppb-form-element-14"><label for="billing_phone">Phone<span class="wppb-required" title="This field is required">*</span></label><input class="text-input " name="billing_phone" type="text" id="billing_phone" value="" autocomplete="off" required=""></li></ul>';
			echo do_shortcode('[sa_verify id="form1" phone_selector="#billing_phone" submit_selector= "register" ]');
		}
	}

	function smsalert_pb_registration_complete( $data, $form_name, $user_id )
	{
		if ( isset( $data['billing_phone'] )) {
			add_user_meta( $user_id, 'billing_phone', $data['billing_phone'],true);
			do_action('smsalert_after_update_new_user_phone', $user_id, $data['billing_phone']);
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
new ProfileRegistrationForm;