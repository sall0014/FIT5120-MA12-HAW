<?php 
if (! defined( 'ABSPATH' )) exit;
class PhoneLogic extends LogicInterface
{
	public function _handle_logic($user_login,$user_email,$phone_number,$otp_type,$form)
	{
		$match = preg_match(SmsAlertConstants::getPhonePattern(),$phone_number);
		switch ($match) 
		{
			case 0:
				$this->_handle_not_matched($phone_number,$otp_type,$form);						break;
			case 1:
				$this->_handle_matched($user_login,$user_email,$phone_number,$otp_type,$form);	break;
		}
	}

	public function _handle_matched($user_login,$user_email,$phone_number,$otp_type,$form)
	{
		$content = (array)json_decode(SmsAlertcURLOTP::smsalert_send_otp_token($form, '', $phone_number), true);
		//$content = array_key_exists('status',$content) ? $content['status'] : '';//commented 17-07-2019
		$status = array_key_exists('status',$content) ? $content['status'] : '';//added 17-07-2019
		//switch ($content) //commented 17-07-2019
		switch ($status) 
		{
			case 'success':
				$this->_handle_otp_sent($user_login,$user_email,$phone_number,$otp_type,$form,$content); 		break;
			default:
				$this->_handle_otp_sent_failed($user_login,$user_email,$phone_number,$otp_type,$form,$content);break;
		}
	}

	public function _handle_not_matched($phone_number,$otp_type,$form)
	{
		SmsAlertUtility::checkSession();

		//$message = str_replace("##phone##",SmsAlertcURLOTP::checkPhoneNos($phone_number),self::_get_otp_invalid_format_message());
		$message = str_replace("##phone##",$phone_number,self::_get_otp_invalid_format_message()); //add on 14-12-2020
		if(self::_is_ajax_form())
			wp_send_json(SmsAlertUtility::_create_json_response($message,SmsAlertConstants::ERROR_JSON_TYPE));
		else
			smsalert_site_otp_validation_form(null,null,null,$message,$otp_type,$form);
	}

	public function _handle_otp_sent_failed($user_login,$user_email,$phone_number,$otp_type,$form,$content)
	{
		SmsAlertUtility::checkSession();
		if(isset($content['description']['desc']))
			$message =$content['description']['desc'];//added 17-07-2019
		elseif(isset($content['description']) && !is_array($content['description']))
			$message =$content['description'];//added 28-01-2021
		else
			$message = str_replace("##phone##",SmsAlertcURLOTP::checkPhoneNos($phone_number),self::_get_otp_sent_failed_message());
		
		if(self::_is_ajax_form())
			wp_send_json(SmsAlertUtility::_create_json_response($message,SmsAlertConstants::ERROR_JSON_TYPE));
		else
			smsalert_site_otp_validation_form(null,null,null,$message,$otp_type,$form);
	}

	public function _handle_otp_sent($user_login,$user_email,$phone_number,$otp_type,$form,$content)
	{
		SmsAlertUtility::checkSession();
		
		if(!empty($_SESSION[FormSessionVars::WP_DEFAULT_LOST_PWD]))
		{
			$number = SmsAlertcURLOTP::checkPhoneNos($phone_number);
			$mob 	= str_repeat("x", strlen($number)-4) . substr($number, -4);
		}
		else
		{
			$mob 	= SmsAlertcURLOTP::checkPhoneNos($phone_number);
		}
		
		$message 	= str_replace("##phone##",$mob,self::_get_otp_sent_message());
		if(self::_is_ajax_form() || $form == 'ajax')
			wp_send_json(SmsAlertUtility::_create_json_response($message,SmsAlertConstants::SUCCESS_JSON_TYPE));
		else
			smsalert_site_otp_validation_form($user_login, $user_email,$phone_number,$message,$otp_type,$form);
	}
	
	public function _get_otp_sent_message()
	{
		return SmsAlertMessages::showMessage('OTP_SENT_PHONE');
	}

	public function _get_otp_sent_failed_message()
	{
		return  sprintf(__("There was an error in sending the OTP to the given Phone Number. Please Try Again or contact site Admin. If you are the website admin, please browse <a href='%s' target='_blank'> here</a> for steps to resolve this error.",'sms-alert'),"https://kb.smsalert.co.in/knowledgebase/unable-to-send-otp-from-wordpress-plugin/");
	}

	public function _get_otp_invalid_format_message()
	{
		return sprintf(__('%sphone%s is not a valid phone number. Please enter a valid Phone Number','sms-alert'), '##', '##');
	}
}