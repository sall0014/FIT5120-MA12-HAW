<?php
if (! defined( 'ABSPATH' )) exit;
if(!is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php')){return;}
class WCSubscription
{
	public function __construct() {
		add_action( 'sa_addTabs', array( $this, 'addTabs' ), 100 );
		add_action( 'woocommerce_subscription_status_updated', array( $this, 'smsalert_send_msg_subs_status_change' ), 10, 3 );
		add_action( 'woocommerce_subscription_renewal_payment_complete', array( $this, 'smsalert_send_msg_subs_renewal' ), 10, 2 );
		 add_action( 'woocommerce_checkout_subscription_created', array( $this, 'smsalert_send_msg_subs_created' ), 10, 3 ); 
	}

	/*add tabs to smsalert settings at backend*/
	public static function addTabs($tabs=array())
	{
		$subscriptions_param = array(
			'checkTemplateFor'	=> 'wc_subscriptions',
			'templates'			=> self::getWC_SubscriptionsTemplates(),
		);

		$tabs['woocommerce']['inner_nav']['wc_subscriptions']['title']		= __("Subscriptions",'sms-alert');
		$tabs['woocommerce']['inner_nav']['wc_subscriptions']['tab_section']= 'subscriptionstemplates';
		$tabs['woocommerce']['inner_nav']['wc_subscriptions']['tabContent']	= self::getContentFromTemplate('views/message-template.php',$subscriptions_param);
		$tabs['woocommerce']['inner_nav']['wc_subscriptions']['icon']		= 'dashicons-products';
		return $tabs;
	}
	
	public static function getContentFromTemplate($path,$params=array())
	{
		return get_smsalert_template($path,$params);
	}

	public static function getWC_SubscriptionsTemplates()
	{
		$smsalert_subs_status_change_admin_msg 			= smsalert_get_option( 'admin_subs_status_change_msg', 'smsalert_general', 'on');
		$sms_body_admin_subs_status_change_msg 			= smsalert_get_option( 'sms_body_admin_subs_status_change_msg', 'smsalert_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_SUBS_STATUS_CHANGE_MSG') );
		
		$smsalert_subs_create_admin_msg 			= smsalert_get_option( 'admin_subs_create_msg', 'smsalert_general', 'on');
		$sms_body_admin_subs_create_msg 			= smsalert_get_option( 'sms_body_admin_subs_create_msg', 'smsalert_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_SUBS_CREATE_MSG') );
		
		$smsalert_subs_renewal_admin_msg 			= smsalert_get_option( 'admin_subs_renewal_msg', 'smsalert_general', 'on');
		$sms_body_admin_subs_renewal_msg 			= smsalert_get_option( 'sms_body_admin_subs_renewal_msg', 'smsalert_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_SUBS_RENEWAL_MSG') );

		$smsalert_subs_status_change_cust_msg 			= smsalert_get_option( 'cust_subs_status_change_msg', 'smsalert_general', 'on');
		$sms_body_cust_subs_status_change_msg 			= smsalert_get_option( 'sms_body_cust_subs_status_change_msg', 'smsalert_message', SmsAlertMessages::showMessage('DEFAULT_CUST_SUBS_STATUS_CHANGE_MSG') );
		
		$smsalert_subs_create_cust_msg 			= smsalert_get_option( 'cust_subs_create_msg', 'smsalert_general', 'on');
		$sms_body_cust_subs_create_msg 			= smsalert_get_option( 'sms_body_cust_subs_create_msg', 'smsalert_message', SmsAlertMessages::showMessage('DEFAULT_CUST_SUBS_CREATE_MSG') );
		
		$smsalert_subs_renewal_cust_msg 			= smsalert_get_option( 'cust_subs_renewal_msg', 'smsalert_general', 'on');
		$sms_body_cust_subs_renewal_msg 			= smsalert_get_option( 'sms_body_cust_subs_renewal_msg', 'smsalert_message', SmsAlertMessages::showMessage('DEFAULT_CUST_SUBS_RENEWAL_MSG') );

		$templates = array();
		
		//subscription create admin msg
		$templates['admin_subs_create']['title'] 			= 'Admin notification when subscrptions created';
		$templates['admin_subs_create']['enabled'] 			= $smsalert_subs_create_admin_msg;
		$templates['admin_subs_create']['status'] 			= 'admin_subs_create';
		$templates['admin_subs_create']['text-body'] 		= $sms_body_admin_subs_create_msg;
		$templates['admin_subs_create']['checkboxNameId'] 	= 'smsalert_general[admin_subs_create_msg]';
		$templates['admin_subs_create']['textareaNameId'] 	= 'smsalert_message[sms_body_admin_subs_create_msg]';
		$templates['admin_subs_create']['token'] 			= array_merge(WooCommerceCheckOutForm::getvariables(),array('[subscription_id]'=>'Subscription Id','[subscription_status]'=>'Subscription Status'));

		//subscription status change admin msg
		$templates['admin_subs_status']['title'] 			= 'Admin notification when subscrptions status change';
		$templates['admin_subs_status']['enabled'] 			= $smsalert_subs_status_change_admin_msg;
		$templates['admin_subs_status']['status'] 			= 'admin_subs_status';
		$templates['admin_subs_status']['text-body'] 		= $sms_body_admin_subs_status_change_msg;
		$templates['admin_subs_status']['checkboxNameId'] 	= 'smsalert_general[admin_subs_status_change_msg]';
		$templates['admin_subs_status']['textareaNameId'] 	= 'smsalert_message[sms_body_admin_subs_status_change_msg]';
		$templates['admin_subs_status']['token'] 			= array_merge(WooCommerceCheckOutForm::getvariables(),array('[subscription_id]'=>'Subscription Id','[subscription_status]'=>'Subscription Status'));
		
		//subscription renewal admin msg
		
		$templates['admin_subs_renewal']['title'] 			= 'Admin notification when subscrptions renew';
		$templates['admin_subs_renewal']['enabled'] 			= $smsalert_subs_renewal_admin_msg;
		$templates['admin_subs_renewal']['status'] 			= 'admin_subs_renewal';
		$templates['admin_subs_renewal']['text-body'] 		= $sms_body_admin_subs_renewal_msg;
		$templates['admin_subs_renewal']['checkboxNameId'] 	= 'smsalert_general[admin_subs_renewal_msg]';
		$templates['admin_subs_renewal']['textareaNameId'] 	= 'smsalert_message[sms_body_admin_subs_renewal_msg]';
		$templates['admin_subs_renewal']['token'] 			= array_merge(WooCommerceCheckOutForm::getvariables(),array('[subscription_id]'=>'Subscription Id','[subscription_status]'=>'Subscription Status'));

		//subscription created customer msg
		$templates['cust_subs_create']['title'] 			= 'Customer notification when subscrptions created';
		$templates['cust_subs_create']['enabled'] 			= $smsalert_subs_create_cust_msg;
		$templates['cust_subs_create']['status'] 			= 'cust_subs_create';
		$templates['cust_subs_create']['text-body'] 		= $sms_body_cust_subs_create_msg;
		$templates['cust_subs_create']['checkboxNameId'] 	= 'smsalert_general[cust_subs_create_msg]';
		$templates['cust_subs_create']['textareaNameId'] 	= 'smsalert_message[sms_body_cust_subs_create_msg]';
		$templates['cust_subs_create']['token'] 			=  array_merge(WooCommerceCheckOutForm::getvariables(),array('[subscription_id]'=>'Subscription Id','[subscription_status]'=>'Subscription Status'));
		
		//subscription status change customer msg
		$templates['cust_subs_status']['title'] 			= 'Customer notification when subscrptions status change';
		$templates['cust_subs_status']['enabled'] 			= $smsalert_subs_status_change_cust_msg;
		$templates['cust_subs_status']['status'] 			= 'cust_subs_status';
		$templates['cust_subs_status']['text-body'] 		= $sms_body_cust_subs_status_change_msg;
		$templates['cust_subs_status']['checkboxNameId'] 	= 'smsalert_general[cust_subs_status_change_msg]';
		$templates['cust_subs_status']['textareaNameId'] 	= 'smsalert_message[sms_body_cust_subs_status_change_msg]';
		$templates['cust_subs_status']['token'] 			=  array_merge(WooCommerceCheckOutForm::getvariables(),array('[subscription_id]'=>'Subscription Id','[subscription_status]'=>'Subscription Status'));
		
		//subscription renewal customer msg
		$templates['cust_subs_renewal']['title'] 			= 'Customer notification when subscrptions renew';
		$templates['cust_subs_renewal']['enabled'] 			= $smsalert_subs_renewal_cust_msg;
		$templates['cust_subs_renewal']['status'] 			= 'cust_subs_renewal';
		$templates['cust_subs_renewal']['text-body'] 		= $sms_body_cust_subs_renewal_msg;
		$templates['cust_subs_renewal']['checkboxNameId'] 	= 'smsalert_general[cust_subs_renewal_msg]';
		$templates['cust_subs_renewal']['textareaNameId'] 	= 'smsalert_message[sms_body_cust_subs_renewal_msg]';
		$templates['cust_subs_renewal']['token'] 			=  array_merge(WooCommerceCheckOutForm::getvariables(),array('[subscription_id]'=>'Subscription Id','[subscription_status]'=>'Subscription Status'));

		return $templates;
	}

	public function smsalert_send_msg_subs_status_change($subscription,$new_status,$old_status)
	{
        $order_id = $subscription->get_parent_id();
		$sms_admin_phone = smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );
        $cust_no = get_post_meta($order_id, '_billing_phone', true);
		$admin_msg = smsalert_get_option( 'sms_body_admin_subs_status_change_msg', 'smsalert_message', '');
		$admin_msg 		 = $this->parse_sms_body($subscription,$admin_msg);
		$admin_sms_data['number'] 	= $sms_admin_phone;
		$admin_sms_data['sms_body'] = $admin_msg;
		$admin_sms_data 			= WooCommerceCheckOutForm::pharse_sms_body($admin_sms_data,$order_id);
		$admin_message 			= (!empty($admin_sms_data['sms_body'])) ? $admin_sms_data['sms_body'] : '';

		$smsalert_notification_subs_status_change_admin_msg = smsalert_get_option( 'admin_subs_status_change_msg', 'smsalert_general', 'on');

		if($smsalert_notification_subs_status_change_admin_msg == 'on' && $admin_message != ''){
			do_action('sa_send_sms', $sms_admin_phone, $admin_message);
		}
		
		$customer_msg = smsalert_get_option( 'sms_body_cust_subs_status_change_msg', 'smsalert_message', '');
		$customer_msg = $this->parse_sms_body($subscription,$customer_msg);
		$cust_sms_data['number'] 	= $cust_no;
		$cust_sms_data['sms_body'] = $customer_msg;
		$cust_sms_data 			= WooCommerceCheckOutForm::pharse_sms_body($cust_sms_data,$order_id);
		$customer_msg 			= (!empty($cust_sms_data['sms_body'])) ? $cust_sms_data['sms_body'] : '';

		$smsalert_notification_subs_status_change_cust_msg = smsalert_get_option( 'cust_subs_status_change_msg', 'smsalert_general', 'on');
		if($smsalert_notification_subs_status_change_cust_msg == 'on' && $customer_msg != ''){
			do_action('sa_send_sms', $cust_no, $customer_msg);
		}
	}
	
	public function smsalert_send_msg_subs_renewal($subscription, $order)
	{
		$order_id = $subscription->get_parent_id();
		$sms_admin_phone = smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );
        $cust_no = get_post_meta($order_id, '_billing_phone', true);
		$admin_msg = smsalert_get_option( 'sms_body_admin_subs_renewal_msg', 'smsalert_message', '');
		$admin_msg 		 = $this->parse_sms_body($subscription,$admin_msg);
		$admin_sms_data['number'] 	= $sms_admin_phone;
		$admin_sms_data['sms_body'] = $admin_msg;
		$admin_sms_data 			= WooCommerceCheckOutForm::pharse_sms_body($admin_sms_data,$order_id);
		$admin_message 			= (!empty($admin_sms_data['sms_body'])) ? $admin_sms_data['sms_body'] : '';

		$smsalert_notification_subs_renewal_admin_msg = smsalert_get_option( 'admin_subs_renewal_msg', 'smsalert_general', 'on');

		if($smsalert_notification_subs_renewal_admin_msg == 'on' && $admin_message != ''){
			do_action('sa_send_sms', $sms_admin_phone, $admin_message);
		}
		
		$customer_msg = smsalert_get_option( 'sms_body_cust_subs_renewal_msg', 'smsalert_message', '');
		$customer_msg = $this->parse_sms_body($subscription,$customer_msg);
		$cust_sms_data['number'] 	= $cust_no;
		$cust_sms_data['sms_body'] = $customer_msg;
		$cust_sms_data 			= WooCommerceCheckOutForm::pharse_sms_body($cust_sms_data,$order_id);
		$customer_msg 			= (!empty($cust_sms_data['sms_body'])) ? $cust_sms_data['sms_body'] : '';

		$smsalert_notification_subs_renewal_cust_msg = smsalert_get_option( 'cust_subs_renewal_msg', 'smsalert_general', 'on');
		if($smsalert_notification_subs_renewal_cust_msg == 'on' && $customer_msg != ''){
			do_action('sa_send_sms', $cust_no, $customer_msg);
		}
	}
	
	public function smsalert_send_msg_subs_created($subscription, $order, $recurring_cart )
	{
        $order_id = $subscription->get_parent_id();
		$sms_admin_phone = smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );
        $cust_no = get_post_meta($order_id, '_billing_phone', true);
		$admin_msg = smsalert_get_option( 'sms_body_admin_subs_create_msg', 'smsalert_message', '');
		$admin_msg 		 = $this->parse_sms_body($subscription,$admin_msg);
		$admin_sms_data['number'] 	= $sms_admin_phone;
		$admin_sms_data['sms_body'] = $admin_msg;
		$admin_sms_data 			= WooCommerceCheckOutForm::pharse_sms_body($admin_sms_data,$order_id);
		$admin_message 			= (!empty($admin_sms_data['sms_body'])) ? $admin_sms_data['sms_body'] : '';

		$smsalert_notification_subs_create_admin_msg = smsalert_get_option( 'admin_subs_create_msg', 'smsalert_general', 'on');

		if($smsalert_notification_subs_create_admin_msg == 'on' && $admin_message != ''){
			do_action('sa_send_sms', $sms_admin_phone, $admin_message);
		}
		
		$customer_msg = smsalert_get_option( 'sms_body_cust_subs_create_msg', 'smsalert_message', '');
		$customer_msg = $this->parse_sms_body($subscription,$customer_msg);
		$cust_sms_data['number'] 	= $cust_no;
		$cust_sms_data['sms_body'] = $customer_msg;
		$cust_sms_data 			= WooCommerceCheckOutForm::pharse_sms_body($cust_sms_data,$order_id);
		$customer_msg 			= (!empty($cust_sms_data['sms_body'])) ? $cust_sms_data['sms_body'] : '';

		$smsalert_notification_subs_create_cust_msg = smsalert_get_option( 'cust_subs_create_msg', 'smsalert_general', 'on');
		if($smsalert_notification_subs_create_cust_msg == 'on' && $customer_msg != ''){
			do_action('sa_send_sms', $cust_no, $customer_msg);
		}
	}

	public function parse_sms_body($subscription, $message){

		$subs_id 	= $subscription->get_id();
		$subs_status 	= $subscription->get_status();

		$find = array(
		    '[subscription_id]', 
            '[subscription_status]'
        );

		$replace = array(
			$subs_id,
			$subs_status
		);

        $message 	= str_replace($find, $replace, $message);
		return $message;
	}
}
new WCSubscription;