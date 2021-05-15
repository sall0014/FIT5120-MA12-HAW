<?php
if (! defined( 'ABSPATH' )) exit;
if (!is_plugin_active( 'pie-register/pie-register.php' ) ) { return; }
class PieRegistrationForm extends FormInterface
{
	private $formSessionVar = FormSessionVars::PIE_REG;
	private $phoneFieldKey;
	
	function handleForm()
	{
		$this->phoneFieldKey = 'billing_phone';
		add_action( 'pie_register_after_register_validate', array($this,'smsalert_pie_user_registration'),99,0);
		add_filter( 'piereg_edit_above_form_data', array($this,'add_short_code_user_verification'),99,1);
	}
	
	public static function isFormEnabled()
	{
		return (smsalert_get_option('buyer_signup_otp', 'smsalert_general')=="on") ? true : false;
	}
	
	function add_short_code_user_verification($form_id)
	{
		
		return do_shortcode("[sa_verify phone_selector='.phone-valid' submit_selector='.pie_register_reg_form .pie_submit']");	
	}
	
	function smsalert_pie_user_registration()
	{
		/* SmsAlertUtility::checkSession();
			if(!array_key_exists($this->formSessionVar,$_SESSION))
			{
			$phone_field = $this->getPhoneFieldKey();
			$phone = !SmsAlertUtility::isBlank($phone_field) ? $_POST[$phone_field] : NULL;
			$this->startTheOTPVerificationProcess($_POST['username'],$_POST['e_mail'],$phone);
			}
			elseif(strcasecmp($_SESSION[$this->formSessionVar],'validated')==0)
			$_SESSION[$this->formSessionVar] = 'validationChecked';
			elseif(strcasecmp($_SESSION[$this->formSessionVar],'validationChecked')==0)
		$this->unsetOTPSessionVariables(); */
	}
	
	function handle_failed_verification($user_login,$user_email,$phone_number)
	{
		/* SmsAlertUtility::checkSession();
		if(!isset($_SESSION[$this->formSessionVar])) return;
		smsalert_site_otp_validation_form($user_login,$user_email,$phone_number,SmsAlertUtility::_get_invalid_otp_method(),"phone",FALSE); */
	}
	
	function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
	{
		/* SmsAlertUtility::checkSession();
		if(!isset($_SESSION[$this->formSessionVar])) return;
		$_SESSION[$this->formSessionVar]="validated"; */
	}
	
	public function unsetOTPSessionVariables()
	{
		unset($_SESSION[$this->txSessionId]);
		unset($_SESSION[$this->formSessionVar]);
	}
	
	public function is_ajax_form_in_play($isAjax)
	{
		return true;
		/* SmsAlertUtility::checkSession();
		return isset($_SESSION[$this->formSessionVar]) ? FALSE : $isAjax; */
	}
	
	function handleFormOptions()
	{
	}	
}
new PieRegistrationForm;