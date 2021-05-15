<?php
if (! defined( 'ABSPATH' )) exit;
if (!is_plugin_active( 'ultimate-member/ultimate-member.php' ) ) { return; }
class UltimateMemberRegistrationForm extends FormInterface
{
	private $formSessionVar 		= FormSessionVars::UM_DEFAULT_REG;
	private $phoneFormID 			= "input[name^='billing_phone']";
	private $formSessionVar2 		= "SA_UM_RESET_PWD";
	private $phoneNumberKey 		= "billing_phone";

	function handleForm()
	{
		if (is_plugin_active( 'ultimate-member/ultimate-member.php' )) //>= UM version 2.0.17
		{
			add_filter( 'um_add_user_frontend_submitted', array($this,'smsalert_um_user_registration'), 1,1);
		}
		else //< UM version 2.0.17
		{
			add_action( 'um_before_new_user_register'	, array($this,'smsalert_um_user_registration'), 1,1);
		}
		add_action( 'um_submit_form_errors_hook_registration'	, array($this,'smsalert_um_registration_validation'), 10 );
		
		if(smsalert_get_option('reset_password', 'smsalert_general')=="on"){
			add_action( 'um_reset_password_process_hook'			, array($this,'smsalert_um_reset_pwd_submitted'),0,1);
		}
		add_action( 'um_registration_complete'					, array($this,'smsalert_um_registration_complete')	, 10, 2 );
		add_action( 'um_after_form'								, array( $this, 'um_form_add_shortcode' ), 10, 1 );
		
		if (!empty($_REQUEST['option']) && $_REQUEST['option']=="smsalert-um-reset-pwd-action") 
		{
			$this->_handle_smsalert_changed_pwd($_POST);
		} 
		
		if (!empty($_REQUEST['sa_um_reset_pwd'])) 
		{
			add_filter( 'um_before_form_is_loaded', array( $this, 'my_before_form'), 10, 1 );
		}		
	}
	
	function _handle_smsalert_changed_pwd($post_data)
	{
		SmsAlertUtility::checkSession();
		$error		      ='';
		$new_password     = !empty($post_data['smsalert_user_newpwd']) ? $post_data['smsalert_user_newpwd'] : '';
		$confirm_password = !empty($post_data['smsalert_user_cnfpwd']) ? $post_data['smsalert_user_cnfpwd'] : '';
		
		if ($new_password=='') {
			$error 		  = SmsAlertMessages::showMessage("ENTER_PWD");
		}
		if ($new_password !== $confirm_password ){
			$error 		  =	SmsAlertMessages::showMessage("PWD_MISMATCH");
		}
		if(!empty($error))
		{
			smsalertAskForResetPassword($_SESSION['user_login'],$_SESSION['phone_number_mo'], $error, 'phone',false);
		}
		
		$user = get_user_by( 'login', $_SESSION['user_login'] );
		reset_password( $user, $new_password );
		$this->unsetOTPSessionVariables();
		exit( wp_redirect( esc_url( add_query_arg( 'sa_um_reset_pwd', true, um_get_core_page('password-reset') ) ) ) );
	}
	

	function um_form_add_shortcode($args){

		$default_login_otp 		= smsalert_get_option('buyer_login_otp', 'smsalert_general');
		$enabled_login_popup 	= smsalert_get_option( 'login_popup', 'smsalert_general');

		if($default_login_otp == 'on' && $enabled_login_popup == 'on'){
			if($args['mode'] == 'login'){
				echo do_shortcode('[sa_verify user_selector="#username-'.$args['form_id'].'" pwd_selector="#user_password-'.$args['form_id'].'" submit_selector="#um-submit-btn"]');
			}
		}
	}

	public static function my_predefined_fields( $predefined_fields )
	{
		$fields = array('billing_phone' => array(
			'title' 	=> 'Smsalert Phone',
			'metakey' 	=> 'billing_phone',
			'type' 		=> 'text',
			'label' 	=> 'Mobile Number',
			'required' 	=> 0,
			'public' 	=> 1,
			'editable' 	=> 1,
			'validate' 	=> 'billing_phone',
			'icon' 		=> 'um-faicon-mobile',
		));
		$predefined_fields = array_merge($predefined_fields,$fields);
		return $predefined_fields;
	}
	
	
	 function my_before_form( $args ) {
		 // your code here
		 echo '<p class="um-notice success"><i class="um-icon-ios-close-empty" onclick="jQuery(this).parent().fadeOut();"></i>' . __("Password Changed Successfully.",'sms-alert') . '</p>';
	}
		 
	function smsalert_um_reset_pwd_submitted( $datas ) {
		
		SmsAlertUtility::checkSession();	
		$user_login = !empty($datas['username_b']) ? $datas['username_b'] : '';
		
		//$user = get_user_by( 'login', $user_login );
		
		if ( username_exists( $user_login ) ) {
			$user = get_user_by( 'login', $user_login );
		} elseif ( email_exists( $user_login ) ) {
			$user = get_user_by( 'email', $user_login );
		}
		$phone_number = get_user_meta($user->data->ID, $this->phoneNumberKey ,true);
		if(!empty($phone_number))
		{
			SmsAlertUtility::initialize_transaction($this->formSessionVar2);
			if($phone_number!='')
			{
				$this->startOtpTransaction($user->data->user_login,$user->data->user_login,null,$phone_number,NULL,NULL);
			}
		}
		return $user;
	}
	
	
	function smsalert_um_registration_validation( $args ) {
		if(smsalert_get_option('allow_multiple_user', 'smsalert_general')!="on" && !SmsAlertUtility::isBlank( $args['billing_phone'] ) ) {
			//if(sizeof(get_users(array('meta_key' => 'billing_phone', 'meta_value' => SmsAlertcURLOTP::checkPhoneNos($args['billing_phone'])))) > 0)
			$getusers = SmsAlertUtility::getUsersByPhone('billing_phone',$args['billing_phone']);
			if(sizeof($getusers) > 0)
			{
				UM()->form()->add_error( 'billing_phone', __( 'An account is already registered with this mobile number. Please login.', 'sms-alert' ));
			}
		}
	}

	function smsalert_um_registration_complete( $user_id, $args ) {
		$user_phone = (!empty($args['billing_phone'])) ? $args['billing_phone'] : '';
		do_action('smsalert_after_update_new_user_phone', $user_id, $user_phone);
	}

	public static function isFormEnabled()
	{
		return (smsalert_get_option('buyer_signup_otp', 'smsalert_general')=="on") ? true : false;
	}

	function smsalert_um_user_registration($args)
	{
		if(!array_key_exists("billing_phone",$args)){
			return $args;
		}
		SmsAlertUtility::checkSession();
		$errors = new WP_Error();

		if(isset($_SESSION['sa_um_mobile_verified']))
		{
			unset($_SESSION['sa_um_mobile_verified']);
			return $args;
		}

		SmsAlertUtility::initialize_transaction($this->formSessionVar);

		foreach ($args as $key => $value)
		{
			if($key=="user_login")
				$username = $value;
			elseif ($key=="user_email")
				$email = $value;
			elseif ($key=="user_password")
				$password = $value;
			elseif ($key == 'billing_phone')
				$phone_number = $value;
			else
				$extra_data[$key]=$value;
		}

		$this->startOtpTransaction($username,$email,$errors,$phone_number,$password,$extra_data);
		exit();
	}

	function startOtpTransaction($username,$email,$errors,$phone_number,$password,$extra_data)
	{
		smsalert_site_challenge_otp($username,$email,$errors,$phone_number,"phone",$password,$extra_data);
	}

	function handle_failed_verification($user_login,$user_email,$phone_number)
	{
		SmsAlertUtility::checkSession();
		if(!isset($_SESSION[$this->formSessionVar]) && !isset($_SESSION[$this->formSessionVar2])) return;
		smsalert_site_otp_validation_form($user_login,$user_email,$phone_number,SmsAlertUtility::_get_invalid_otp_method(),"phone",FALSE);
	}

	function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
	{
		SmsAlertUtility::checkSession();
		if(!isset($_SESSION[$this->formSessionVar]) && !isset($_SESSION[$this->formSessionVar2])) return;
		
		if(isset($_SESSION[$this->formSessionVar2]))
			smsalertAskForResetPassword($_SESSION['user_login'],$_SESSION['phone_number_mo'], SmsAlertMessages::showMessage("CHANGE_PWD"), 'phone',false,'smsalert-um-reset-pwd-action');
		else
			$_SESSION['sa_um_mobile_verified']=true;
	}

	public function unsetOTPSessionVariables()
	{
		unset($_SESSION[$this->txSessionId]);
		unset($_SESSION[$this->formSessionVar]);
		unset($_SESSION[$this->formSessionVar2]);
	}

	public function is_ajax_form_in_play($isAjax)
	{
		SmsAlertUtility::checkSession();
		return isset($_SESSION[$this->formSessionVar]) ? FALSE : $isAjax;
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
new UltimateMemberRegistrationForm;