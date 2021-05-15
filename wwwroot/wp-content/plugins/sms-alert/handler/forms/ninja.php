<?php
if (! defined( 'ABSPATH' )) exit;
if (!is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) { return; }
class SmsAlertNinjaForms extends FormInterface
{
	private $formSessionVar = FormSessionVars::NF_FORMS;
	private $formPhoneVer 	= FormSessionVars::NF_PHONE_VER;
	private $phoneFormID;

    function handleForm()
    {
		add_action( 'ninja_forms_after_form_display', array($this,'enqueue_nj_form_script'),  100 );

		add_action('ninja_forms_localize_field_settings_submit', array($this, '_add_custom_button'), 99, 2);

		add_action('ninja_forms_localize_field_settings_phone', array($this, '_add_class_phone_field'), 99, 2);

		add_action( 'ninja_forms_after_submission', __CLASS__ . '::smsalert_send_sms_form_submit', 10, 1);

		$this->routeData();
    }

	function sa_ninja_handle_js_script()
	{
		$otp_resend_timer = smsalert_get_option( 'otp_resend_timer', 'smsalert_general', '15');

		echo '<script>

		jQuery(document).on("click", ".nf-form-cont .sa-otp-btn-init",function(event){
			event.stopImmediatePropagation();
			jQuery(this).parents(".smsalertModal").hide();
			var e = jQuery(this).parents("form").find(".sa-phone-field").val();
			var data = {user_phone:e};
			var action_url = "'.site_url().'/?option=smsalert-nj-ajax-verify";
			saInitOTPProcess(this,action_url, data,'.$otp_resend_timer.');
		});

		jQuery(document).on("click", ".nf-form-cont .smsalert_otp_validate_submit",function(event){
			event.stopImmediatePropagation();
			var current_form = jQuery(this).parents("form");
			var action_url = "'.site_url().'/?option=smsalert-validate-otp-form";
			var data = current_form.serialize()+"&otp_type=phone&from_both=";
			sa_validateOTP(this,action_url,data,function(){
				current_form.find(".sa-hide").trigger("click")
			});
			return false;
		});
		</script>';
	}

	public function _add_class_phone_field($settings,$form)
	{
		$formId 			= $form->get_id();
		$form_enable 		= smsalert_get_option( 'ninja_order_status_'.$formId, 'smsalert_ninja_general', 'on');
		$otp_enable 		= smsalert_get_option( 'ninja_otp_'.$formId, 'smsalert_ninja_general', 'on');
		$phone_field 		=  smsalert_get_option( 'ninja_sms_phone_'.$formId, 'smsalert_ninja_general', '' );
		$otp_template_style =  smsalert_get_option( 'otp_template_style', 'smsalert_general', 'otp-popup-1.php');

		if($settings['key']==$phone_field)
		{
			$settings['element_class'] = 	'sa-phone-field phone-valid';
		}
		return $settings;
	}

	public function _add_custom_button($settings,$form)
	{
		$formId 			= $form->get_id();
		$form_enable 		= smsalert_get_option( 'ninja_order_status_'.$formId, 'smsalert_ninja_general', 'on');
		$otp_enable			= smsalert_get_option( 'ninja_otp_'.$formId, 'smsalert_ninja_general', 'on');
		$phone_field		=  smsalert_get_option( 'ninja_sms_phone_'.$formId, 'smsalert_ninja_general', '' );
		$otp_template_style =  smsalert_get_option( 'otp_template_style', 'smsalert_general', 'otp-popup-1.php');
		if($form_enable== 'on' && $otp_enable=='on')
		{
			$settings['element_class'] = 	'sa-hide';

			$settings['afterField']='
				<div id="nf-field-4-container" class="nf-field-container submit-container  label-above ">
					<div class="nf-before-field">
						<nf-section></nf-section>
					</div>
					<div class="nf-field">
						<div class="field-wrap submit-wrap">
							<div class="nf-field-label"></div>
							<div class="nf-field-element">
								<input class="sa-otp-btn-init ninja-forms-field nf-element smsalert_otp_btn_submit" value="'.__("Verify & Submit","sms-alert").'" type="button">
							</div>
						</div>
					</div>
				<style>.sa-hide{display:none !important}</style>
				';

			$settings['afterField'] .= get_smsalert_template('template/'.$otp_template_style,$params=array());
			$settings['afterField'] .= $this->sa_ninja_handle_js_script();
		}
		return $settings;
	}

    function routeData()
	{
		if(!array_key_exists('option', $_GET)) return;
		switch (trim($_GET['option']))
		{
			case "smsalert-nj-ajax-verify":
				$this->_send_otp_nj_ajax_verify($_POST);
				exit();
				break;
		}
	}

	function _send_otp_nj_ajax_verify($getdata)
	{
		SmsAlertUtility::checkSession();
		SmsAlertUtility::initialize_transaction($this->formSessionVar);

		if(array_key_exists('user_phone', $getdata) && !SmsAlertUtility::isBlank($getdata['user_phone']))
		{
			$_SESSION[$this->formPhoneVer] = trim($getdata['user_phone']);
			$message = str_replace("##phone##",$getdata['user_phone'],SmsAlertMessages::showMessage('OTP_SENT_PHONE'));
			smsalert_site_challenge_otp('test',null,null,trim($getdata['user_phone']),"phone",null,null,true);
		}
		else
		{
			wp_send_json( SmsAlertUtility::_create_json_response(__("Enter a number in the following format : 9xxxxxxxxx",'sms-alert'),SmsAlertConstants::ERROR_JSON_TYPE) );
		}
	}

	function enqueue_nj_form_script()
	{
		wp_register_script( 'smsalert-auth', SA_MOV_URL . 'js/otp-sms.min.js', array('jquery'), SmsAlertConstants::SA_VERSION, true );
		wp_localize_script('smsalert-auth', 'sa_notices',array(
			'waiting_txt'	=> __("Please wait...","sms-alert"),
		));
		wp_enqueue_script('smsalert-auth');
	}

	function isFormEnabled()
	{
		return is_plugin_active( 'ninja-forms/ninja-forms.php') ? true : false ;
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
		unset($_SESSION[$this->formSessionVar]);
		unset($_SESSION[$this->formPhoneVer]);
	}

	public function is_ajax_form_in_play($isAjax)
	{
		SmsAlertUtility::checkSession();
		return isset($_SESSION[$this->formSessionVar]) ? TRUE : $isAjax;
	}

	public function getPhoneNumberSelector($selector)
	{
		SmsAlertUtility::checkSession();
		if(self::isFormEnabled()) array_push($selector, $this->phoneFormID);
		return $selector;
	}

	function handleFormOptions()
	{
		if(is_plugin_active( 'ninja-forms/ninja-forms.php'))
		{
			add_filter('sAlertDefaultSettings',  __CLASS__ .'::addDefaultSetting',1,2);
			add_action( 'sa_addTabs', array( $this, 'addTabs' ), 10 );
		}
	}

	/*add tabs to smsalert settings at backend*/
	public static function addTabs($tabs=array())
	{
		$tabs['ninja']['nav']			= 'Ninja Form';
		$tabs['ninja']['icon']			= 'dashicons-list-view';

		$tabs['ninja']['inner_nav']['ninja_admin']['title']	= 'Admin Notificataions';
		$tabs['ninja']['inner_nav']['ninja_admin']['tab_section']	= 'ninjaadmintemplates';
		$tabs['ninja']['inner_nav']['ninja_admin']['first_active']	= true;
		$tabs['ninja']['inner_nav']['ninja_admin']['tabContent']    = self::getContentFromTemplate('views/ninja_admin_template.php');

		$tabs['ninja']['inner_nav']['ninja_admin']['icon']	= 'dashicons-list-view';
		$tabs['ninja']['inner_nav']['ninja_cust']['title']	= 'Customer Notificataions';
		$tabs['ninja']['inner_nav']['ninja_cust']['tab_section']   	= 'ninjacsttemplates';
		$tabs['ninja']['inner_nav']['ninja_cust']['tabContent']	 	= self::getContentFromTemplate('views/ninja_customer_template.php');
		$tabs['ninja']['inner_nav']['ninja_cust']['icon']	= 'dashicons-admin-users';
		return $tabs;
	}

	public static function getContentFromTemplate($path,$params=array())
	{
		return get_smsalert_template($path,$params);
	}

	/*get variables*/
	public static function getNinjavariables($form_id=null,$fields=false)
	{
		$variables 	= array();
		$form 		= Ninja_Forms()->form($form_id)->get();
		$form_name 	= $form->get_settings();

		foreach ( $form_name['formContentData'] as $form ) {
			if(!is_array($form))
			{
				$variables['['.$form.']'] = $form;
			}
		}
		if($fields)
		{
			return $form_name['formContentData'];
		}
		$ret_string = '';
		foreach($variables as $vk => $vv)
		{
			$ret_string .= sprintf( "<a href='#' val='%s'>%s</a> | " , $vk , __($vv,'sms-alert'));
		}
		return $ret_string;
   }

	/*add default settings to savesetting in setting-options*/
	public function addDefaultSetting($defaults=array())
	{
		$wpam_statuses=self::get_ninja_forms();
		foreach($wpam_statuses as $ks => $vs)
		{
			$defaults['smsalert_ninja_general']['ninja_admin_notification_'.$ks]='off';
			$defaults['smsalert_ninja_general']['ninja_order_status_'.$ks]	='off';
			$defaults['smsalert_ninja_general']['ninja_message_'.$ks]		='off';
			$defaults['smsalert_ninja_message']['ninja_admin_sms_body_'.$ks]='';
			$defaults['smsalert_ninja_message']['ninja_sms_body_'.$ks]		='';$defaults['smsalert_ninja_general']['ninja_sms_phone_'.$ks]	 ='';
			$defaults['smsalert_ninja_general']['ninja_sms_otp_'.$ks]		='';
			$defaults['smsalert_ninja_general']['ninja_otp_'.$ks]			='';
			$defaults['smsalert_ninja_message']['ninja_otp_sms_'.$ks]		='';
		}
		return $defaults;
	}

	public static function get_ninja_forms()
	{
		$ninja_forms = array();
		$forms = Ninja_Forms()->form()->get_forms();
		foreach ( $forms as $form ) {
			$form_id 	= $form->get_id();
			$ninja_forms[$form_id]=$form->get_setting('title');
		}
		return $ninja_forms;
	}

	public static function parse_sms_content($content=NULL,$datas=array())
	{
		$find 		= array_keys($datas);
		$replace 	= array_values($datas);
		$content 	= str_replace( $find, $replace, $content );
		return $content;
	}

	public static  function smsalert_send_sms_form_submit( $form_data) {
		$datas=array();
		if(!empty($form_data))
		{
			$billing_phone = '';
			$phone_field =  smsalert_get_option( 'ninja_sms_phone_'.$form_data['form_id'], 'smsalert_ninja_general', '' );
			foreach( $form_data[ 'fields' ] as $field )
			{
				$datas['['.$field[ 'key' ].']'] = $field[ 'value' ];
				if($field[ 'key' ]==$phone_field)
				{
					$billing_phone = $field[ 'value' ];
				}
			}
			$form_enable 		= smsalert_get_option( 'ninja_message_'.$form_data['form_id'], 'smsalert_ninja_general', 'on');
			$buyer_sms_notify 	= smsalert_get_option( 'ninja_order_status_'.$form_data['form_id'], 'smsalert_ninja_general', 'on');
			$admin_sms_notify 	= smsalert_get_option( 'ninja_admin_notification_'.$form_data['form_id'], 'smsalert_ninja_general', 'on');

			if($form_enable=='on' && $buyer_sms_notify=='on')
			{
				if($billing_phone!='')
				{
				   $buyer_sms_content = smsalert_get_option( 'ninja_sms_body_'.$form_data['form_id'], 'smsalert_ninja_message', '');
				   do_action('sa_send_sms', $billing_phone, self::parse_sms_content($buyer_sms_content,$datas));
				}
			}

			if($admin_sms_notify=='on')
			{
				$admin_phone_number     = smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );
				if($admin_phone_number!='')
				{
					$admin_sms_content = smsalert_get_option( 'ninja_admin_sms_body_'.$form_data['form_id'], 'smsalert_ninja_message', '');
					do_action('sa_send_sms', $admin_phone_number, self::parse_sms_content($admin_sms_content,$datas));
				}
			}
		}
	}
}
new SmsAlertNinjaForms;