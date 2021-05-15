<?php
if (! defined( 'ABSPATH' )) exit;
if(!is_plugin_active('new-user-approve/new-user-approve.php')){return;}
class NewUserApprove
{
	public function __construct() {
		add_filter( 'sAlertDefaultSettings',  __CLASS__ .'::addDefaultSetting',1);
		add_action( 'new_user_approve_user_approved', array( $this, 'send_sms_approved' ),1 );
		add_action( 'new_user_approve_user_denied', array( $this, 'send_sms_denied' ),1 );
		add_action( 'sa_addTabs', array( $this, 'addTabs' ), 100);
	}

	/*add tabs to smsalert settings at backend*/
	public static function addTabs($tabs=array())
	{
		$newuserapprove_param = array(
			'checkTemplateFor'	=> 'newuserapprove',
			'templates'			=> self::getNewUserApproveTemplates(),
		);

		$tabs['user_registration']['inner_nav']['newuserapprove']['title']		  = 'New User Approve';
		$tabs['user_registration']['inner_nav']['newuserapprove']['tab_section']  = 'cartbountytemplates';
		$tabs['user_registration']['inner_nav']['newuserapprove']['tabContent']	  = self::getContentFromTemplate('views/message-template.php',$newuserapprove_param);
		$tabs['user_registration']['inner_nav']['newuserapprove']['icon']		  = 'dashicons-admin-users';
		return $tabs;
	}

	public static function getContentFromTemplate($path,$params=array())
	{
		return get_smsalert_template($path,$params);
	}

	/*add default settings to savesetting in setting-options*/
	public static function addDefaultSetting($defaults=array())
	{
		$defaults['smsalert_nua_general']['approved_notify'] = 'off';
		$defaults['smsalert_nua_message']['approved_notify'] = '';
		$defaults['smsalert_nua_general']['denied_notify']	 = 'off';
		$defaults['smsalert_nua_message']['denied_notify']	 = '';
		return $defaults;
	}

	public static function getNewUserApproveTemplates()
	{
		//customer template
		$current_val 		= smsalert_get_option( 'approved_notify', 'smsalert_nua_general', 'on');
		$checkboxNameId		= 'smsalert_nua_general[approved_notify]';
		$textareaNameId		= 'smsalert_nua_message[approved_notify]';
		$text_body 			= smsalert_get_option( 'approved_notify', 'smsalert_nua_message', SmsAlertMessages::showMessage('DEFAULT_NEW_USER_APPROVED'));

		$templates 			= array();

		$templates['approved']['title'] 		 = 'When account is Approved';
		$templates['approved']['enabled'] 		 = $current_val;
		$templates['approved']['status'] 		 = 'approved';
		$templates['approved']['text-body'] 	 = $text_body;
		$templates['approved']['checkboxNameId'] = $checkboxNameId;
		$templates['approved']['textareaNameId'] = $textareaNameId;
		$templates['approved']['token'] 		 = self::getNewUserApprovevariables();

		//admin template
		$current_val 		= smsalert_get_option('denied_notify', 'smsalert_nua_general', 'on');
		$checkboxNameId		= 'smsalert_nua_general[denied_notify]';
		$textareaNameId		= 'smsalert_nua_message[denied_notify]';
		$text_body 			= smsalert_get_option('denied_notify', 'smsalert_nua_message', SmsAlertMessages::showMessage('DEFAULT_NEW_USER_REJECTED'));

		$templates['deny']['title'] 			= 'When account is Deny';
		$templates['deny']['enabled'] 			= $current_val;
		$templates['deny']['status'] 			= 'deny';
		$templates['deny']['text-body'] 		= $text_body;
		$templates['deny']['checkboxNameId']	= $checkboxNameId;
		$templates['deny']['textareaNameId']	= $textareaNameId;
		$templates['deny']['token'] 			= self::getNewUserApprovevariables();

		return $templates;
	}

	public function send_sms_approved($user_id)
	{
		$user 	= new WP_User( $user_id );
		$phone 	= get_the_author_meta('billing_phone', $user->ID);

		$smsalert_nua_approved_notify 	= smsalert_get_option( 'approved_notify', 'smsalert_nua_general', 'on');
		$smsalert_nua_approved_message 	= smsalert_get_option( 'approved_notify', 'smsalert_nua_message', '' );

		if($smsalert_nua_approved_notify == 'on' && $smsalert_nua_approved_message != ''){
			do_action('sa_send_sms', $phone, $this->parse_sms_body($user_id,$smsalert_nua_approved_message));
		}
	}

	public function send_sms_denied($user_id)
	{
		$user 	= new WP_User( $user_id );

		$phone 	= get_the_author_meta('billing_phone', $user->ID);

		$smsalert_nua_denied_notify 	= smsalert_get_option( 'denied_notify', 'smsalert_nua_general', 'on');
		$smsalert_nua_denied_message 	= smsalert_get_option( 'denied_notify', 'smsalert_nua_message', '' );

		if($smsalert_nua_denied_notify == 'on' && $smsalert_nua_denied_message != ''){
			do_action('sa_send_sms', $phone, $this->parse_sms_body($user_id,$smsalert_nua_denied_message));
		}
	}

	public static function getNewUserApprovevariables()
	{
		$variables = array(
			'[username]' 	=> 'Username',
			'[store_name]'  => 'Store Name',
		);
		return $variables;
	}

	public function parse_sms_body($data=array(),$content=null)
	{
		$user 		= new WP_User( $data );
		$username 	= $user->user_login;

		$find = array(
            '[username]',
            '[store_name]',
        );

		$replace = array(
			$username,
			get_bloginfo(),
		);

        $content = str_replace( $find, $replace, $content );
		return $content;
	}
}
new NewUserApprove;