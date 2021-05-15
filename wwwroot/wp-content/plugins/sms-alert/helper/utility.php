<?php
if (! defined( 'ABSPATH' )) exit;
class SmsAlertUtility
{	
	public static function get_hidden_phone($phone)
	{
		$hidden_phone = 'xxxxxxx' . substr($phone,strlen($phone) - 3);
		return $hidden_phone;
	}
	
	public static function isBlank( $value )
	{
		if( ! isset( $value ) || empty( $value ))
		//if((! isset( $value ) || empty( $value )) || (is_array($value) && in_array('',$value)))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function _create_json_response($message,$type)
	{
		return array( 'message' => $message, 'result' => $type);
	}
	
	public static function mo_is_curl_installed()
	{
		if  (in_array  ('curl', get_loaded_extensions()))
			return 1;
		else 
			return 0;
	}

	public static function currentPageUrl()
	{
		$pageURL = 'http';

		if ((isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on"))
			$pageURL .= "s";

		$pageURL .= "://";

		if ($_SERVER["SERVER_PORT"] != "80")
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];

		else
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

		if ( function_exists('apply_filters') ) apply_filters('wppb_curpageurl', $pageURL);

        return $pageURL;
	}
	
	public static function mo_get_hiden_email($email)
	{
        if(!isset($email) || trim($email)===''){
			return "";
		}
		$emailsize = strlen($email);
		$partialemail = substr($email,0,1);
		$temp = strrpos($email,"@");
		$endemail = substr($email,$temp-1,$emailsize);
		for($i=1;$i<$temp;$i++){
			$partialemail = $partialemail . 'x';
		}
		$hiddenemail = $partialemail . $endemail;
               
        return $hiddenemail;
    }

	public static function validatePhoneNumber($phone)
	{
		if(!preg_match(SmsAlertConstants::getPhonePattern(),$phone,$matches))
			return false;
		else
			return true;
	}
	
	public static function checkSession()
	{
		if(version_compare(phpversion(), '5.4.0', '>='))
		{
			$session_enabled = ((session_status() !== PHP_SESSION_ACTIVE) || (session_status() === PHP_SESSION_NONE)) ? FALSE : TRUE;
		}
		else
		{
			$session_enabled= (session_id() === '') ? FALSE : TRUE;
		}			
		if (!$session_enabled){
			session_start();
		}
		
		/* if (session_id() == '' || !isset($_SESSION)){
			session_start();
		} */
	}
	
	// date format 29/04/20
	public function parseAttributesFromTag($tag){
		$pattern = '/(\w+)=[\'"]([^\'"]*)/';

		preg_match_all($pattern,$tag,$matches,PREG_SET_ORDER);

		$result = array();
		foreach($matches as $match){
			$attrName = $match[1];
			$attrValue = is_numeric($match[2])? (int)$match[2]: trim($match[2]);

			$result[$attrName] = $attrValue;
		}
		return $result;
	}

	public static function initialize_transaction($form,$sessionValue = true)
	{
		SmsAlertUtility::checkSession();
		$reflect = new ReflectionClass('FormSessionVars');
		foreach ($reflect->getConstants()  as $key => $value)
			unset($_SESSION[$value]);
		$_SESSION[$form] = $sessionValue;
	}

	public static function _get_invalid_otp_method()
	{
		return SmsAlertMessages::showMessage('INVALID_OTP');
	}
	
	public static function get_otp_length(){
		$otp_template = smsalert_get_option( 'sms_otp_send', 'smsalert_message', '');
		
		if (strpos($otp_template, 'length') !== false) {
			$position 	= strpos($otp_template,"length");
			$otp_length = substr($otp_template,$position+8,1);
			return $otp_length;
		}		
		return 4;
	}
	
	//for number validator
	public static function enqueue_script_for_intellinput(){
		if(smsalert_get_option('checkout_show_country_code', 'smsalert_general')=="on"){
			
			$dep = apply_filters('intel_dep',array('jquery'));
			
			wp_enqueue_script('sa_pv_intl-phones-lib',SA_MOV_URL .'js/intlTelInput-jquery.min.js' , $dep ,SmsAlertConstants::SA_VERSION,true);
			wp_enqueue_script('wccheckout_utils',SA_MOV_URL .'js/utils.js',array('jquery') ,SmsAlertConstants::SA_VERSION,true);
			wp_enqueue_script('wccheckout_default',SA_MOV_URL .'js/phone-number-validate.js',array('sa_pv_intl-phones-lib'),SmsAlertConstants::SA_VERSION, true);
			
			wp_localize_script( 'sa_pv_intl-phones-lib', 'sa_default_countrycode',smsalert_get_option('default_country_code', 'smsalert_general'));
			
			wp_enqueue_style('wpv_telinputcss_style',SA_MOV_URL .'css/intlTelInput.min.css',array(),SmsAlertConstants::SA_VERSION, false);
		}
	}
	
	//to check user billiing_phone from database
	public static function getUsersByPhone($key,$value,$extra_datas=array())
	{
		if(empty($value))
		{
			return false;
		}
		else
		{
			$wcc_ph 		= SmsAlertcURLOTP::checkPhoneNos($value);
			$wocc_ph    	= SmsAlertcURLOTP::checkPhoneNos($value,false);
			$wth_pls_ph    	= '+'.$wcc_ph;
			
			$datas = array('meta_key' => 'billing_phone', 'meta_value' => array($wcc_ph,$wocc_ph,$wth_pls_ph));
			foreach($extra_datas as $e_key =>  $e_val)
			{
				$datas[$e_key] = $e_val;
			}
			$getusers = get_users($datas);
			return $getusers;
		}
	}
	
	public static function formatNumberForCountryCode($phoneNum){
		$country_code_enabled = smsalert_get_option( 'checkout_show_country_code', 'smsalert_general' );
		if($country_code_enabled=='on' && !empty($phoneNum))
		{
			return "+".SmsAlertcURLOTP::checkPhoneNos($phoneNum); 
		}else{
			return $phoneNum;
		}
	}
	
	//SmsAlertUtility::checkCompatibility();
	public static function checkCompatibility()
	{
		$path = session_save_path();
		$obj=array();
		if(is_writable($path))
		{
			$obj[]= "Yes, session path $path is writable.";
			
		}
		else
		{
			$obj[]= "No, session path $path is not writable.";
		}
		
		return $obj;
	}
	
	
}