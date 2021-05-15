<?php
if (! defined( 'ABSPATH' )) exit;
if (!is_plugin_active( 'wp-job-manager/wp-job-manager.php' ) ) { return; }

class WpJob extends FormInterface
{  
	private $formSessionVar = FormSessionVars::WP_JOB_MANAGER;
	
	function handleForm()
	{
		add_action( 'create_job_application_notification_recipient', array( $this, 'new_job_application_send_sms' ), 10, 3 );
		add_action('pending_to_publish', array($this,'listing_published_send_sms'));
        add_action('pending_payment_to_publish', array($this,'listing_published_send_sms'));
		add_filter( 'submit_job_form_fields', array($this,'frontend_add_phone_field'));
		add_action('job_manager_job_submitted', array( $this, 'send_new_job_notification'));
		add_action( 'job_manager_user_edit_job_listing', array( $this, 'send_updated_job_notification' ) );
		add_action('wpjm_notify_new_user', array($this, 'sa_update_billing_phone'), 10, 3 );
	}
	
	public function new_job_application_send_sms( $send_to, $job_id, $application_id ) {
		
		$post = get_post($job_id);
        $user_info = get_userdata($post->post_author);
		$admin_number = smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );
		$phone = get_user_meta( $user_info->ID, 'billing_phone', true );
		$candidate_phone = get_user_meta( get_post_meta($application_id,'_candidate_user_id',true), 'billing_phone', true );
		$msg_enable  = get_option('smsalert_sms_notification');
		$new_application_customer_msg_enable  = get_option('smsalert_new_application_sms_status');
		$new_application_admin_msg_enable  = get_option('smsalert_new_application_admin_sms_status');
		$new_application_candidate_msg_enable  = get_option('smsalert_new_application_candidate_sms_status');
		
		if($msg_enable==1)
		{
			$visitor_message = get_option('smsalert_new_application_sms');
			$admin_message = get_option('smsalert_new_application_admin_sms');
			$candidate_message = get_option('smsalert_new_application_candidate_sms');
			
			$datas = array();
			$datas['[username]'] = $user_info->user_login;
			$datas['[user_email]'] = $user_info->username;
			$datas['[phone]'] = $phone;
			$datas['[candidate_name]'] = get_post_meta($application_id,'Full name',true);
			$datas['[candidate_email]'] = get_post_meta($application_id,'_candidate_email',true);
			$datas['[job_id]'] = $job_id;
			$datas['[job_name]'] = get_post_meta($application_id,'_job_applied_for',true);
			$datas['[store_name]'] = get_bloginfo();
		
			if($visitor_message!='' && $new_application_customer_msg_enable=='1')
			{
			  do_action('sa_send_sms', $phone, self::parse_sms_content($visitor_message,$datas));
			}
			
			if($candidate_message!='' && $new_application_candidate_msg_enable=='1' && $candidate_phone!='')
			{
			  do_action('sa_send_sms', $candidate_phone, self::parse_sms_content($candidate_message,$datas));
			}
			
			if($admin_number!='' && $admin_message!='' && $new_application_admin_msg_enable=='1')
			{
			  do_action('sa_send_sms', $admin_number, self::parse_sms_content($admin_message,$datas));
			}		
		}
	}
	
	//check this function once
	function sa_update_billing_phone( $user_id, $password, $new_user ) {
		if ( isset( $_POST['job_phone'] ) )
			update_user_meta($user_id, 'billing_phone', $_POST['job_phone']);
	}
	
	function listing_published_send_sms($job_id) {
		if( 'job_listing' != get_post_type( $job_id ) ) {
			return;
		}
		$post = get_post($job_id);
		$user_info = get_userdata($post->post_author);

		$admin_number = smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );
		$phone = get_user_meta( $user_info->ID, 'billing_phone', true );
		
		$msg_enable  = get_option('smsalert_sms_notification');
		$approve_customer_msg_enable  = get_option('smsalert_job_approve_customer_sms_status');
		$approve_admin_msg_enable  = get_option('smsalert_job_approve_sms_status');
		
		if($msg_enable==1)
		{
			$visitor_message = get_option('smsalert_job_approve_customer_sms');
			$admin_message = get_option('smsalert_job_approve_sms');
			
			$datas = array();
			$datas['[username]'] = $user_info->user_login;
			$datas['[user_email]'] = $user_info->username;
			$datas['[phone]'] = $phone;
			$datas['[job_id]'] = $post->ID;
			$datas['[job_name]'] = $post->post_title;
			$datas['[store_name]'] = get_bloginfo();
			if($visitor_message!='' && $approve_customer_msg_enable=='1')
			{
			  do_action('sa_send_sms', $phone, self::parse_sms_content($visitor_message,$datas));
			}
			if($admin_number!='' && $admin_message!='' && $approve_admin_msg_enable=='1')
			{
			  do_action('sa_send_sms', $admin_number, self::parse_sms_content($admin_message,$datas));
			}		
		}
}
	
	
	function frontend_add_phone_field( $fields ) {
		if(!is_user_logged_in())
		{
		  $fields['job']['job_phone'] = array(
			'label'       => __( 'Phone', 'job_manager' ),
			'type'        => 'text',
			'required'    => true,
			'placeholder' => 'Enter Mobile Number',
			'priority'    => 7
		  );
		}
		return $fields;
    }
	
	public function job_manager_settings($settings) {
		
		$data=array();
		$settings['smsalert'][] ='SMS Alert' ;
		
		$data[] = array('name'=>'smsalert_sms_notification','cb_label'=>__('Enable to send sms notification to admin as well as employer','sms-alert'),'std'=> get_option( 'smsalert_sms_notification',1),'label'=>__('SMS Notification','sms-alert'),'type'=>'checkbox');
		
		$data[] = array('name'=>'smsalert_new_job_sms_status','cb_label'=>__('Enable Admin Message When New Job Submitted','sms-alert'),'std'=> get_option( 'smsalert_new_job_sms_status',1),'label'=>__('','sms-alert'),'type'=>'checkbox');
		
		$data[] = array('name'=>'smsalert_new_job_sms','std'=>'Dear admin, a new job [job_name] is submitted by [username].Please check your admin dashboard for complete details.','label'=>__('','sms-alert'),'desc'=>__('You can use following tokens [store_name], [job_id], [job_name] ,[username] ,[email] ,[phone]','sms-alert'),'type'=>'textarea');
		
		$data[] = array('name'=>'smsalert_job_approve_sms_status','cb_label'=>__('Enable Admin Message When A Job Approved','sms-alert'),'std'=> get_option( 'smsalert_job_approve_sms_status',1),'label'=>__('','sms-alert'),'type'=>'checkbox');
		
		$data[] = array('name'=>'smsalert_job_approve_sms','std'=>'Dear admin, a new job [job_name] is approved.Please check your admin dashboard for complete details.','label'=>__('','sms-alert'),'desc'=>__('You can use following tokens [store_name], [job_id], [job_name] ,[username] ,[email] ,[phone]','sms-alert'),'type'=>'textarea');
		
		$data[] = array('name'=>'smsalert_edit_job_sms_status','cb_label'=>__('Enable Admin Message When Job Edited','sms-alert'),'std'=> get_option( 'smsalert_edit_job_sms_status',1),'label'=>__('','sms-alert'),'type'=>'checkbox');
		
		$data[] = array('name'=>'smsalert_edit_job_sms','std'=>'Dear admin, a job [job_name] is updated by [username].Please check your admin dashboard for complete details.','label'=>__('','sms-alert'),'desc'=>__('You can use following tokens [store_name], [job_id], [job_name] ,[username] ,[email] ,[phone]','sms-alert'),'type'=>'textarea');
		
		$data[] = array('name'=>'smsalert_new_application_admin_sms_status','cb_label'=>__('Enable Admin Message When New Application Submitted','sms-alert'),'std'=> get_option( 'smsalert_new_application_admin_sms_status',1),'label'=>__('','sms-alert'),'type'=>'checkbox');
		
		$data[] = array('name'=>'smsalert_new_application_admin_sms','std'=>'Dear [username], a candidate [candidate_name] is applied for job.','label'=>__('','sms-alert'),'desc'=>__('You can use following tokens [store_name], [job_id], [job_name] ,[username] ,[email] ,[phone] ,[candidate_name] ,[candidate_email]','sms-alert'),'type'=>'textarea');
		
		$data[] = array('name'=>'smsalert_new_job_customer_sms_status','cb_label'=>__('Enable Employer Message When New Job Submitted','sms-alert'),'std'=> get_option( 'smsalert_new_job_customer_sms_status',1),'label'=>__('','sms-alert'),'type'=>'checkbox');
		
		$data[] = array('name'=>'smsalert_new_job_customer_sms','std'=>'Dear [username], Thank you for sumitting job, please wait for approval.','label'=>__('','sms-alert'),'desc'=>__('You can use following tokens [store_name], [job_id], [job_name] ,[username] ,[email] ,[phone]','sms-alert'),'type'=>'textarea');
		
		$data[] = array('name'=>'smsalert_job_approve_customer_sms_status','cb_label'=>__('Enable Employer Message When A Job Approved','sms-alert'),'std'=> get_option( 'smsalert_job_approve_customer_sms_status',1),'label'=>__('','sms-alert'),'type'=>'checkbox');
		
		$data[] = array('name'=>'smsalert_job_approve_customer_sms','std'=>'Dear [username], your job [job_name] is approved.','label'=>__('','sms-alert'),'desc'=>__('You can use following tokens [store_name], [job_id], [job_name] ,[username] ,[email] ,[phone]','sms-alert'),'type'=>'textarea');
		
		$data[] = array('name'=>'smsalert_edit_job_customer_sms_status','cb_label'=>__('Enable Employer Message When Job Edited','sms-alert'),'std'=> get_option( 'smsalert_edit_job_customer_sms_status',1),'label'=>__('','sms-alert'),'type'=>'checkbox');
		
		$data[] = array('name'=>'smsalert_edit_job_customer_sms','std'=>'Dear [username], job [job_name] is updated successfully.','label'=>__('','sms-alert'),'desc'=>__('You can use following tokens [store_name], [job_id], [job_name] ,[username] ,[email] ,[phone]','sms-alert'),'type'=>'textarea');
		
		$data[] = array('name'=>'smsalert_new_application_sms_status','cb_label'=>__('Enable Employer Message When New Application Submitted','sms-alert'),'std'=> get_option( 'smsalert_new_application_sms_status',1),'label'=>__('','sms-alert'),'type'=>'checkbox');
		
		$data[] = array('name'=>'smsalert_new_application_sms','std'=>'Dear [username], a candidate [candidate_name] is applied for job.','label'=>__('','sms-alert'),'desc'=>__('You can use following tokens [store_name], [job_id], [job_name] ,[username] ,[email] ,[phone] ,[candidate_name] ,[candidate_email]','sms-alert'),'type'=>'textarea');
		
		$data[] = array('name'=>'smsalert_new_application_candidate_sms_status','cb_label'=>__('Enable Candidate Message When New Application Submitted','sms-alert'),'std'=> get_option( 'smsalert_new_application_sms_status',1),'label'=>__('','sms-alert'),'type'=>'checkbox');
		
		$data[] = array('name'=>'smsalert_new_application_candidate_sms','std'=>'Hello [candidate_name], Thank you for submitting the application with [store_name].
Powered by
www.smsalert.co.in','label'=>__('','sms-alert'),'desc'=>__('You can use following tokens [store_name], [job_id], [job_name] ,[username] ,[email] ,[phone] ,[candidate_name] ,[candidate_email]','sms-alert'),'type'=>'textarea');
		
		$settings['smsalert'][] = $data;

		return $settings;
	}
		
	function send_new_job_notification($job_id) {
		$post = get_post($job_id);
        $user_info = get_userdata($post->post_author);
		$admin_number = smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );
		$phone = get_user_meta( $user_info->ID, 'billing_phone', true );
		$msg_enable  = get_option('smsalert_sms_notification');
		$new_job_customer_msg_enable  = get_option('smsalert_new_job_customer_sms_status');
		$new_job_admin_msg_enable  = get_option('smsalert_new_job_sms_status');
		
		if($msg_enable==1)
		{
			$visitor_message = get_option('smsalert_new_job_customer_sms');
			$admin_message = get_option('smsalert_new_job_sms');
			
			$datas = array();
			$datas['[username]'] = $user_info->user_login;
			$datas['[user_email]'] = $user_info->username;
			$datas['[phone]'] = $phone;
			$datas['[job_id]'] = $post->ID;
			$datas['[job_name]'] = $post->post_title;
			$datas['[store_name]'] = get_bloginfo();
			if($visitor_message!='' && $new_job_customer_msg_enable=='1')
			{
			  do_action('sa_send_sms', $phone, self::parse_sms_content($visitor_message,$datas));
			}
			if($admin_number!='' && $admin_message!='' && $new_job_admin_msg_enable=='1')
			{
			  do_action('sa_send_sms', $admin_number, self::parse_sms_content($admin_message,$datas));
			}		
		}
	}
	
	function send_updated_job_notification($job_id) {
		$post = get_post($job_id);
        $user_info = get_userdata($post->post_author);
		$admin_number = smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );
		$phone = get_user_meta( $user_info->ID, 'billing_phone', true );
		
		$msg_enable  = get_option('smsalert_sms_notification');
		$edit_job_customer_msg_enable  = get_option('smsalert_edit_job_customer_sms_status');
		$edit_job_admin_msg_enable  = get_option('smsalert_edit_job_sms_status');
		
		if($msg_enable==1)
		{
			$visitor_message = get_option('smsalert_edit_job_customer_sms');
			$admin_message = get_option('smsalert_edit_job_sms');
			
			$datas = array();
			$datas['[username]'] = $user_info->user_login;
			$datas['[user_email]'] = $user_info->username;
			$datas['[phone]'] = $phone;
			$datas['[job_id]'] = $post->ID;
			$datas['[job_name]'] = $post->post_title;
			$datas['[store_name]'] = get_bloginfo();
			if($visitor_message!='' && $edit_job_customer_msg_enable=='1')
			{
			  do_action('sa_send_sms', $phone, self::parse_sms_content($visitor_message,$datas));
			}
			if($admin_number!='' && $admin_message!='' && $edit_job_admin_msg_enable=='1')
			{
				do_action('sa_send_sms', $admin_number, self::parse_sms_content($admin_message,$datas));
			}		
		}
	}

	public static function isFormEnabled() 
	{
		return is_plugin_active( 'wp-job-manager/wp-job-manager.php') ? true : false;
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
		add_filter( 'job_manager_settings', array( $this, 'job_manager_settings'));
	}
}
new WpJob;