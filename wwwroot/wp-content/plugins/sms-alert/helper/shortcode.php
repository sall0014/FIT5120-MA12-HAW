<?php
if (! defined( 'ABSPATH' )) exit;

class SAVerify
{
	public static $response_array = array();
	private $formSessionVar = FormSessionVars::SA_SHORTCODE_FORM_VERIFY;

	public function __construct()
	{
		add_shortcode('sa_verify', array($this,'sa_verify_form'), 100);
		add_action('otp_verification_failed', array($this,'handle_failed_verification'), 10,3);
		add_action('otp_verification_successful', array($this,'handle_post_verification'), 10,6);
		$this->routeData();
		add_action('sa_enqueue_otp_js',array($this,'enqueue_otp_js_script'));
		add_filter( 'sa_ajax', array($this,'is_ajax_form_in_play'), 1,1);
	}

	function sa_verify_form($callback){
		$html = '';
		$phone_selector 		= (!empty($callback['phone_selector'])) ? $callback['phone_selector'] : '';
		$submit_selector 		= (!empty($callback['submit_selector'])) ? $callback['submit_selector'] : '';
		$username_selector 		= (!empty($callback['user_selector'])) ? $callback['user_selector'] : '';
		$password_selector 		= (!empty($callback['pwd_selector'])) ? $callback['pwd_selector'] : '';

		if (!empty($submit_selector) && !preg_match('/[#.]/', $submit_selector)) {
			$submit_selector = 'input[name='.$submit_selector.']';
		}

		if (!empty($phone_selector) && !preg_match('/[#.]/', $phone_selector)) {
			$phone_selector = 'input[name='.$phone_selector.']';
		}

		$otp_resend_timer 	= smsalert_get_option( 'otp_resend_timer', 'smsalert_general', '15');
		$otp_template_style =  smsalert_get_option( 'otp_template_style', 'smsalert_general', 'otp-popup-1.php');

		do_action('sa_enqueue_otp_js');

		$template = get_smsalert_template('template/'.$otp_template_style,$params=array());
		$html.= $template;

		$html.='<style>.sa-default-btn-hide{display:none !important}}</style><script>
		jQuery(window).on(\'load\', function(){
			var button = jQuery("'.$submit_selector.'");
			jQuery("'.$submit_selector.'").addClass("sa-default-btn-hide");
			jQuery("'.$submit_selector.'").after(button.clone()).addClass("sa-otp-btn-init smsalert_otp_btn_submit").html();		jQuery(".sa-otp-btn-init").attr("id","sa_verify").attr("name","sa_verify");
			jQuery(".sa-otp-btn-init").removeClass("sa-default-btn-hide");
			jQuery("'.$phone_selector.'").addClass("phone-valid");
		});
		jQuery(document).off("click").on("click", ".smsalert_otp_btn_submit",function(){
			
			jQuery(this).parents(".smsalertModal").hide();
			var e 			= jQuery(this).parents("form").find("'.$phone_selector.'").val();
			var u 			= jQuery(this).parents("form").find("'.$username_selector.'").val();
			var p			= jQuery(this).parents("form").find("'.$password_selector.'").val();
			if(typeof u != "undefined" && typeof p != "undefined")
			{
				var data 	= {username:u,password:p};
			}
			else
			{
				var data 	= {user_phone:e};
			}

			jQuery(this).parents("form").find("input, select, .wpcf7-validates-as-required").not(".otp_input").each(function(){
				jQuery(this).removeClass("sa_field_error");
				if(jQuery(this).is(":hidden")){
					return true;
				}
				
				if((jQuery(this).attr("aria-required") || jQuery(this).attr("required")) && jQuery(this).val() == ""){
					jQuery(this).addClass("sa_field_error");
				} 
				
				if(!jQuery(this).hasClass("sa_field_error") && jQuery(this).attr("minlength")){

					var char_length = jQuery(this).val().length;

					if(char_length < jQuery(this).attr("minlength")){
						jQuery(this).addClass("sa_field_error");
					}
				}

				if(!jQuery(this).hasClass("sa_field_error") && jQuery(this).attr("maxlength")){

					var char_length = jQuery(this).val().length;

					if(char_length > jQuery(this).attr("minlength")){
						jQuery(this).addClass("sa_field_error");
					}
				}

				if(!jQuery(this).hasClass("sa_field_error") && jQuery(this).hasClass("wpcf7-tel")){

					var pattern = "/^[+]?[0-9() -]*$/";
					var tel_num = jQuery(this).val();

					if(!tel_num.match(pattern) && jQuery(this).val() == ""){
						jQuery(this).addClass("sa_field_error");
					}
				}
				
				if(!jQuery(this).hasClass("sa_field_error") && jQuery(this).hasClass("wpcf7-url")){
	
					var url = jQuery(this).val();
					
					if(!url.match(/^http([s]?):\/\/.*/)){
						jQuery(this).addClass("sa_field_error");
					}
				}
				
				if(!jQuery(this).hasClass("sa_field_error") && jQuery(this).hasClass("wpcf7-email")){
	
					var email = jQuery(this).val();
					if(!email.match(/^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i)){
						jQuery(this).addClass("sa_field_error");
					}
				}

				if(!jQuery(this).hasClass("sa_field_error") && jQuery(this).hasClass("wpcf7-file")){
					var fileName 	= jQuery(this).val();
					var img_ext 	= jQuery(this).attr("accept");
					if(fileName!="" && img_ext!="")
					{
						var validExt 	= img_ext.replace(/\./g,"").split(",");
						var fileNameExt = fileName.substr(fileName.lastIndexOf(".") + 1);
						if (fileNameExt!="" && jQuery.inArray(fileNameExt, validExt) == -1)
						{
							jQuery(this).addClass("sa_field_error");
						}
					}
				}
				
				if(jQuery(this).is("input[type=checkbox]")){
				if(jQuery(this).parents("form").find(".wpcf7-form-control-wrap .wpcf7-validates-as-required input[type=checkbox]:checked").length > 0){
						jQuery(this).parents(".wpcf7-validates-as-required").removeClass("sa_field_error");
					}else{
						jQuery(this).parents(".wpcf7-validates-as-required").addClass("sa_field_error");
					}
				}
			});
			
			if(jQuery(this).parents("form").find(".sa_field_error").length == 0)
			{
			
				var action_url 	= "'.site_url().'/?option='.(($username_selector!='' && $password_selector!='') ? 'smsalert_ajax_login_popup' : 'smsalert-shortcode-ajax-verify').'";
				saInitOTPProcess(this,action_url, data,'.$otp_resend_timer.');
				return false;
			}
		});

		jQuery(document).on("click", ".smsalert_otp_validate_submit",function(){
			var current_form 	= jQuery(this).parents("form");
			var action_url 		= "'.site_url().'/?option=smsalert-validate-otp-form";
			var data 			= current_form.serialize()+"&otp_type=phone&from_both=";
			sa_validateOTP(this,action_url,data,function(){
				current_form.find(".sa-default-btn-hide").not(".sa-otp-btn-init").trigger("click")
			});
			return false;
		});
		</script>';
		return $html;
	}

	public function is_ajax_form_in_play($isAjax)
	{
		SmsAlertUtility::checkSession();
		return isset($_SESSION[$this->formSessionVar]) ? FALSE : $isAjax;
	}

	function routeData()
	{
		if(!array_key_exists('option', $_GET)) return;
		switch (trim($_GET['option']))
		{
			case "smsalert-shortcode-ajax-verify":
				$this->_send_otp_shortcode_ajax_verify($_POST);
				exit();
				break;

			case "smsalert-validate-otp-form":
				$this->shortcode_otp_validate($_POST);
				exit();
				break;
		}
	}

	function shortcode_otp_validate($data){

		do_action('smsalert_validate_otp','smsalert_customer_validation_otp_token');
	}

	function _send_otp_shortcode_ajax_verify($getdata)
	{
		global $phoneLogic;
		SmsAlertUtility::checkSession();
		SmsAlertUtility::initialize_transaction($this->formSessionVar);

		$message = str_replace("##phone##",$getdata['user_phone'],$phoneLogic->_get_otp_invalid_format_message());

		$phone 		= SmsAlertcURLOTP::checkPhoneNos($getdata['user_phone']);

		if(array_key_exists('user_phone', $getdata) && !SmsAlertUtility::isBlank($getdata['user_phone']) && !empty($phone))
		{
			$_SESSION[$this->formSessionVar] = $phone;
			smsalert_site_challenge_otp('test',null,null,$phone,"phone",null,null,'ajax');
		}
		else
		{
			wp_send_json(SmsAlertUtility::_create_json_response($message,SmsAlertConstants::ERROR_JSON_TYPE));
		}
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

	function enqueue_otp_js_script()
	{
		wp_register_script( 'smsalert-auth', SA_MOV_URL . 'js/otp-sms.min.js', array('jquery'), SmsAlertConstants::SA_VERSION, true );
		wp_localize_script('smsalert-auth', 'sa_notices',array(
			'waiting_txt'		=> __("Please wait...","sms-alert"),
			'enter_here'		=> __("Enter Number Here","sms-alert"),
		));
		wp_enqueue_script('smsalert-auth');
		SmsAlertUtility::enqueue_script_for_intellinput();
	}

	function unsetOTPSessionVariables(){

		unset($_SESSION[$this->txSessionId]);
		unset($_SESSION[$this->formSessionVar]);
	}
}
new SAVerify;