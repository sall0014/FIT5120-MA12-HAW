<?php
if (! defined( 'ABSPATH' )) exit;
if(!is_plugin_active('woocommerce/woocommerce.php')){return;}
class WCReview
{
	public function __construct() {
		add_filter( 'sAlertDefaultSettings',  __CLASS__ .'::addDefaultSetting',1);
		add_action( 'sa_addTabs', array( $this, 'addTabs' ), 100);
		add_action( 'woocommerce_order_status_changed', array( $this, 'schedule_sms' ), 100,4 );
	}

	public function schedule_sms($order_id,  $old_status,  $new_status, $instance ){

		$order 			 = new WC_Order($order_id);
		$order_items 	 = $order->get_items();
		$first_item 	 = current($order_items);
		$post_id 		 = $first_item['order_id'];
		$buyer_no 		 = get_post_meta($post_id, '_billing_phone', true);

		$customer_notify = smsalert_get_option( 'customer_notify', 'smsalert_or_general', 'on');
		$review_message  = smsalert_get_option('customer_notify', 'smsalert_or_message','');
		$message_status	 = smsalert_get_option('review_status', 'smsalert_review');
		$days			 = smsalert_get_option('schedule_day', 'smsalert_review');

		if($message_status== $new_status && $customer_notify == 'on' && $review_message != '' && $order->get_parent_id() == 0){

			$time_enabled		        = smsalert_get_option('send_at', 'smsalert_review');

			if($time_enabled            == 'on'){
				$schedule_time	        = smsalert_get_option('schedule_time', 'smsalert_review');

				$date_modified 	        = $order->get_date_modified();
				$default_time 	        = $date_modified->date("Y-m-d").' '.$schedule_time;

				$schedule 		        = date('Y-m-d H:i:s',strtotime('+'.$days.' days',strtotime($default_time)));

				}else{
				date_default_timezone_set('Asia/Kolkata');

				$order_time		        = date('Y-m-d H:i:s');
				$schedule 		        = date('Y-m-d H:i:s',strtotime('+'.$days.' days',strtotime($order_time)));
			}
			$buyer_sms_data['number'] 	= $buyer_no;
			$buyer_sms_data['sms_body'] = $review_message;
			$buyer_sms_data 			= WooCommerceCheckOutForm::pharse_sms_body($buyer_sms_data,$order_id);
			$review_message 			= (!empty($buyer_sms_data['sms_body'])) ? $buyer_sms_data['sms_body'] : '';

			do_action('sa_send_sms', $buyer_no,$review_message, $schedule);
		}
	}

	/*add tabs to smsalert settings at backend*/
	public static function addTabs($tabs=array())
	{
		$review_param    = array(
			'checkTemplateFor' => 'review',
			'templates'        => self::getReviewTemplates(),
		);

		$tabs['woocommerce']['inner_nav']['review']['title'] = 'Review Request';
		$tabs['woocommerce']['inner_nav']['review']['tab_section']  = 'reviewtemplates';
		$tabs['woocommerce']['inner_nav']['review']['tabContent']	= self::getContentFromTemplate('views/review-template.php',$review_param);
		return $tabs;
	}

	public static function getContentFromTemplate($path,$params=array())
	{
		return get_smsalert_template($path,$params);
	}

	/*add default settings to savesetting in setting-options*/
	public static function addDefaultSetting($defaults=array())
	{
		$defaults['smsalert_review']['schedule_day']		= '1';
		$defaults['smsalert_review']['review_status']		= 'completed';
		$defaults['smsalert_review']['schedule_time']		= '10:00';
		$defaults['smsalert_review']['send_at']				= 'off';
		$defaults['smsalert_or_general']['customer_notify']	= 'off';
		$defaults['smsalert_or_message']['customer_notify']	= '';
		return $defaults;
	}

	public static function getReviewTemplates()
	{
		$current_val 		        = smsalert_get_option( 'customer_notify', 'smsalert_or_general', 'on');
		$checkboxNameId		        = 'smsalert_or_general[customer_notify]';
		$textareaNameId		        = 'smsalert_or_message[customer_notify]';
		$text_body                  = smsalert_get_option('customer_notify', 'smsalert_or_message',SmsAlertMessages::showMessage('DEFAULT_CUSTOMER_REVIEW_MESSAGE'));

		$templates 					= array();

		$templates['title'] 		= 'Request for Review';
		$templates['enabled'] 		= $current_val;
		$templates['text-body'] 	= $text_body;
		$templates['checkboxNameId']= $checkboxNameId;
		$templates['textareaNameId']= $textareaNameId;
		$templates['moreoption'] 	= 1;
		$templates['token'] 		= WooCommerceCheckOutForm::getvariables();
		return $templates;
	}
}
new WCReview;