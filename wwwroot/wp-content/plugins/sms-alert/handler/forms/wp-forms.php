<?php
if (! defined( 'ABSPATH' )) exit;

if (!is_plugin_active( 'wpforms-lite/wpforms.php' ) && !is_plugin_active( 'wpforms/wpforms.php')) { return; }

class WpForm extends FormInterface
{  
	private $formSessionVar = FormSessionVars::WP_DEFAULT_REG;
	
	function handleForm()
	{
		add_action( 'wpforms_process_complete', array( $this, 'wpf_dev_process_complete' ), 10, 4 );
		add_filter( 'wpforms_display_field_after',array( $this, 'wpf_dev_process_filter'), 10, 2 );
		add_action('wpforms_form_settings_panel_content', array( $this, 'custom_wpforms_form_settings_panel_content'), 10, 1);
		add_filter('wpforms_builder_settings_sections',array( $this, 'custom_wpforms_builder_settings_sections'), 10, 2);
	}
	
	function wpf_dev_process_filter( $field, $form_data ) {
	   $msg_enable  = $form_data['smsalert']['message_enable'];
		if($msg_enable==1)
		{
			$phone_field = $form_data['settings']['smsalert']['visitor_phone'];	
			$phone_field_id=preg_replace('/[^0-9]/', '', $phone_field);
			if($field['id']==$phone_field_id)
			{				
				echo do_shortcode('[sa_verify id="form1" phone_selector="#wpforms-'.$form_data['id'].'-field_'.$field['id'].'" submit_selector= ".wpforms-submit" ]');
			}
		}
    }
	function custom_wpforms_builder_settings_sections( $sections, $form_data ){ 
	   $sections['smsalert']='SMS Alert';
	   return $sections;
     } 

	function custom_wpforms_form_settings_panel_content( $instance ){ 
	     echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-smsalert">';
		 
        echo '<div class="wpforms-panel-content-section-title"><span id="wpforms-builder-settings-notifications-title">SMS Alert Message Configuration</span></div>';
		wpforms_panel_field(
			'select',
			'smsalert',
			'message_enable',
			$instance->form_data,
			esc_html__( 'Message', 'wpforms-lite' ),
			array(
				'default' => '1',
				'options' => array(
					'1' => esc_html__( 'On', 'wpforms-lite' ),
					'0' => esc_html__( 'Off', 'wpforms-lite' ),
				),
			)
		);
	wpforms_panel_field(
					'text',
					'smsalert',
					'admin_number',
					$instance->form_data,
					esc_html__( '
Send Admin SMS To', 'wpforms-lite' ),
					array(
						'default'    => '',
						'parent'     => 'settings',
						'after'      => '<p class="note">' .
										sprintf(
											esc_html__( 'Admin order sms notifications will be send in this number. Enter multiple numbers by comma separated' )
										) .
										'</p>',
					)
				);	
			wpforms_panel_field(
					'textarea',
					'smsalert',
					'admin_message',
					$instance->form_data,
					esc_html__( 'Admin Message', 'wpforms-lite' ),
					array(
						'rows'       => 6,
						'default'    => '[store_name]: Hello admin, a new user has submitted the form.',
						'smarttags'  => array(
							'type' => 'all',
						),
						'parent'     => 'settings',
						'class'      => 'email-msg',
						
					)
				);
			wpforms_panel_field(
					'text',
					'smsalert',
					'visitor_phone',
					$instance->form_data,
					esc_html__( 'Select Phone Field', 'wpforms-lite' ),
					array(
						'default'    => '',
						'smarttags'  => array(
							'type' => 'all',
						),
						'parent'     => 'settings'
					)
				);
			wpforms_panel_field(
					'textarea',
					'smsalert',
					'visitor_message',
					$instance->form_data,
					esc_html__( 'Visitor Message', 'wpforms-lite' ),
					array(
						'rows'       => 6,
						'default'    => 'Thank you for contacting us.',
						'smarttags'  => array(
							'type' => 'all',
						),
						'parent'     => 'settings',
						'class'      => 'email-msg',
					)
				);
		echo '</div>';
    }
		
	function wpf_dev_process_complete($fields, $entry, $form_data, $entry_id) {
		$msg_enable  = $form_data['smsalert']['message_enable'];
		$phone_field_id='';
		if($msg_enable==1)
		{
			$phone_field = $form_data['settings']['smsalert']['visitor_phone'];
			$admin_number = $form_data['settings']['smsalert']['admin_number'];
			$visitor_message = $form_data['settings']['smsalert']['visitor_message'];
			$admin_message = $form_data['settings']['smsalert']['admin_message'];
			$phone_field_id=preg_replace('/[^0-9]/', '', $phone_field);
			if($phone_field_id!='')
			{
				$phone='';$datas = array();
				foreach($fields as $key=>$field)
				{
					$datas['{field_id="'.$key.'"}'] = $field['value'];
					if($phone_field_id==$key)
					{
						$phone = $field['value'];
					}
				}
				do_action('sa_send_sms', $phone, self::parse_sms_content($visitor_message,$datas));
				if($admin_number!='')
				{
					do_action('sa_send_sms', $admin_number, self::parse_sms_content($admin_message,$datas));
				}
			}		
		}
	}

	public static function isFormEnabled() 
	{
		return (is_plugin_active( 'wpforms-lite/wpforms.php') || is_plugin_active( 'wpforms/wpforms.php')) ? true : false;		
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

    public static function parse_sms_content($content=NULL,$datas=array())
	{
		$find 		= array_keys($datas);
		$replace 	= array_values($datas);
		$content 	= str_replace( $find, $replace, $content );
		return $content;
	}

	function handleFormOptions()
	{
	}
}
new WpForm;