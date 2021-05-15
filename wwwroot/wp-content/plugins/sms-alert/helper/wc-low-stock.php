<?php
if (! defined( 'ABSPATH' )) exit;
if(!is_plugin_active('woocommerce/woocommerce.php')){return;}
class WCLowStock
{
	public function __construct() {
		add_action( 'sa_addTabs', array( $this, 'addTabs' ), 100 );
		add_action( 'woocommerce_low_stock', array( $this, 'smsalert_send_msg_low_stock' ), 11 );
		add_action( 'woocommerce_no_stock', array( $this, 'smsalert_send_msg_out_of_stock' ), 10 );
	}

	/*add tabs to smsalert settings at backend*/
	public static function addTabs($tabs=array())
	{
		$backinstock_param = array(
			'checkTemplateFor'	=> 'wc_stocknotification',
			'templates'			=> self::getWC_StockNotificationTemplates(),
		);

		$tabs['woocommerce']['inner_nav']['wc_stocknotification']['title']		= __("Stock Notifications",'sms-alert');
		$tabs['woocommerce']['inner_nav']['wc_stocknotification']['tab_section']= 'backinstocktemplates';
		$tabs['woocommerce']['inner_nav']['wc_stocknotification']['tabContent']	= self::getContentFromTemplate('views/message-template.php',$backinstock_param);
		$tabs['woocommerce']['inner_nav']['wc_stocknotification']['icon']		= 'dashicons-products';
		return $tabs;
	}
	
	public static function getContentFromTemplate($path,$params=array())
	{
		return get_smsalert_template($path,$params);
	}

	public static function getWC_StockNotificationTemplates()
	{
		$smsalert_low_stock_admin_msg 			= smsalert_get_option( 'admin_low_stock_msg', 'smsalert_general', 'on');
		$sms_body_admin_low_stock_msg 			= smsalert_get_option( 'sms_body_admin_low_stock_msg', 'smsalert_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_LOW_STOCK_MSG') );

		$smsalert_out_of_stock_admin_msg 		= smsalert_get_option( 'admin_out_of_stock_msg', 'smsalert_general', 'on');
		$sms_body_admin_out_of_stock_msg 		= smsalert_get_option( 'sms_body_admin_out_of_stock_msg', 'smsalert_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_OUT_OF_STOCK_MSG') );

		$templates = array();

		//low stock
		$low_stock_variables = array(
			'[item_name]' 		=> 'Product Name',
			'[store_name]' 		=> 'Store Name',
			'[item_qty]' 		=> 'Quantity',
			'[shop_url]' 		=> 'Shop Url',
		);
		$templates['low-stock']['title'] 			= 'When product is in low stock';
		$templates['low-stock']['enabled'] 			= $smsalert_low_stock_admin_msg;
		$templates['low-stock']['status'] 			= 'low-stock';
		$templates['low-stock']['text-body'] 		= $sms_body_admin_low_stock_msg;
		$templates['low-stock']['checkboxNameId'] 	= 'smsalert_general[admin_low_stock_msg]';
		$templates['low-stock']['textareaNameId'] 	= 'smsalert_message[sms_body_admin_low_stock_msg]';
		$templates['low-stock']['token'] 			= $low_stock_variables;

		//out of stock
		$out_of_stock_variables = array(
			'[item_name]' 		=> 'Product Name',
			'[store_name]' 		=> 'Store Name',
			'[item_qty]' 		=> 'Quantity',
			'[shop_url]' 		=> 'Shop Url',
		);
		$templates['out-of-stock']['title'] 		= 'When product is out of stock';
		$templates['out-of-stock']['enabled'] 		= $smsalert_out_of_stock_admin_msg;
		$templates['out-of-stock']['status'] 		= 'out-of-stock';
		$templates['out-of-stock']['text-body'] 	= $sms_body_admin_out_of_stock_msg;
		$templates['out-of-stock']['checkboxNameId']= 'smsalert_general[admin_out_of_stock_msg]';
		$templates['out-of-stock']['textareaNameId']= 'smsalert_message[sms_body_admin_out_of_stock_msg]';
		$templates['out-of-stock']['token'] 		= $out_of_stock_variables;

		return $templates;
	}

	public function smsalert_send_msg_low_stock($product)
	{
		$message 		 = smsalert_get_option( 'sms_body_admin_low_stock_msg', 'smsalert_message', '' );
        $message 		 = $this->parse_sms_body($product,$message);

		$sms_admin_phone = smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );

		$smsalert_notification_low_stock_admin_msg = smsalert_get_option( 'admin_low_stock_msg', 'smsalert_general', 'on');

		if($smsalert_notification_low_stock_admin_msg == 'on' && $message != ''){
			//send sms to post author
			$admin_phone_number 			= str_replace('postauthor','post_author',$sms_admin_phone);
			$author_no = $this->get_vendor_number($product);
			if((strpos($admin_phone_number,'post_author') !== false) && $author_no!='')
			{
				$admin_phone_number = str_replace('post_author', $author_no, $admin_phone_number);
			}
			
			do_action('sa_send_sms', $admin_phone_number, $message);
		}
	}

	public function smsalert_send_msg_out_of_stock($product)
	{
		$message 		 = smsalert_get_option( 'sms_body_admin_out_of_stock_msg', 'smsalert_message', '' );
		$message 		 = $this->parse_sms_body($product,$message);

		$sms_admin_phone = smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );

		$smsalert_notification_out_of_stock_admin_msg = smsalert_get_option( 'admin_out_of_stock_msg', 'smsalert_general', 'on');

		if($smsalert_notification_out_of_stock_admin_msg == 'on' && $message != ''){
			//send sms to post author
			$admin_phone_number 			= str_replace('postauthor','post_author',$sms_admin_phone);
			$author_no = $this->get_vendor_number($product);
			if((strpos($admin_phone_number,'post_author') !== false) && $author_no!='')
			{
				$admin_phone_number = str_replace('post_author', $author_no, $admin_phone_number);
			}
			
			do_action('sa_send_sms', $admin_phone_number, $message);
		}
	}

	public function parse_sms_body($product, $message){

		$item_name 				= $product->get_name();
		$item_qty 				= $product->get_stock_quantity();

		$find = array(
            '[item_name]',
            '[item_qty]',
            '[store_name]',
            '[shop_url]',
        );

		$replace = array(
			$item_name,
			$item_qty,
			get_bloginfo(),
			get_site_url(),
		);

        $message 				= str_replace($find, $replace, $message);
		return $message;
	}
	
	public function get_vendor_number($product){

		$product_id 			= $product->get_id();
		$author_no 				= get_the_author_meta('billing_phone', get_post($product_id)->post_author);
		return $author_no;
	}
}
new WCLowStock;