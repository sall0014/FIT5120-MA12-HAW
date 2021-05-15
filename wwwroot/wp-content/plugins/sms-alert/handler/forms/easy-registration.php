<?php
if (! defined( 'ABSPATH' )) exit;
if (!is_plugin_active( 'easy-registration-forms/erforms.php' ) ) { return; }
class EasyRegistrationForm extends FormInterface
{
	public static $response_array = array();

	private $formSessionVar = FormSessionVars::ER_DEFAULT_REG;

	function handleForm()
	{
		add_action('erf_post_submission_completed', array($this,'smsalert_er_registration_complete'),20);
		add_filter('erf_process_form_html',array($this,'sa_er_handle_js_script'),100,2);
		add_filter('intel_dep',array($this,'set_dependency_intl'),10,1);
		$this->routeData();
	}

	function set_dependency_intl($param){

		if(is_plugin_active( 'easy-registration-forms/erforms.php' ))
		{
			return array_merge($param,array("intl-tel-input"));
		}else{
			return $param;
		}
	}

	function sa_er_handle_js_script($html,$form)
	{
		if(smsalert_get_option('buyer_signup_otp', 'smsalert_general')=="on")
		{
			$fields = erforms_get_form_input_fields($form['id']);
			$search = array();
			$replace= array();
			foreach($fields as $field){
				if(array_key_exists('addUserFieldMap',$field) && $field['addUserFieldMap']=='billing_phone')
				{
					array_push($search,"id='".$field['name']."'");
					array_push($replace,"id='billing_phone'");
				}
			}
			$html = str_ireplace($search, $replace, $html);

			$html.= do_shortcode('[sa_verify phone_selector="#billing_phone" submit_selector= ".btn" ]');
		}
		//$html .= $this->enqueue_otp_js_script();
		return $html;
	 }

	function routeData()
	{
		if(!array_key_exists('option', $_GET)) return;
		switch (trim($_GET['option']))
		{
			case "smsalert-er-ajax-verify":
				$this->_send_otp_er_ajax_verify($_POST);
				exit();
				break;
		}
	}

	function _send_otp_er_ajax_verify($getdata)
	{
		SmsAlertUtility::checkSession();
		SmsAlertUtility::initialize_transaction($this->formSessionVar);

		if(array_key_exists('user_phone', $getdata) && !SmsAlertUtility::isBlank($getdata['user_phone']))
		{
			$_SESSION[$this->formSessionVar] = trim($getdata['user_phone']);
			$message = str_replace("##phone##",$getdata['user_phone'],SmsAlertMessages::showMessage('OTP_SENT_PHONE'));
			smsalert_site_challenge_otp('test',null,null,trim($getdata['user_phone']),"phone",null,null,true);
		}
		else
		{
			wp_send_json(SmsAlertUtility::_create_json_response('Enter a number in the following format : 9xxxxxxxxx',SmsAlertConstants::ERROR_JSON_TYPE));
		}
	}

	function smsalert_er_registration_complete($submission_id) {
		$submission= erforms()->submission->get_submission($submission_id);
		$user_id = $submission['user']['ID'];
		$user_phone = get_user_meta( $user_id, 'billing_phone', true );
		if($user_phone!='')
		{
		  do_action('smsalert_after_update_new_user_phone', $user_id, $user_phone);
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

	// function enqueue_otp_js_script()
	// {
		// wp_register_script( 'smsalert-auth', SA_MOV_URL . 'js/otp-sms.min.js', array('jquery'), SmsAlertConstants::SA_VERSION, true );
		// wp_enqueue_script('smsalert-auth');
	// }

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
new EasyRegistrationForm;