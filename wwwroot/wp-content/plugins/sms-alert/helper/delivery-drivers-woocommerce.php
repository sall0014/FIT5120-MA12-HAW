<?php
if (! defined( 'ABSPATH' )) exit;
if(!is_plugin_active('delivery-drivers-for-woocommerce/delivery-drivers-for-woocommerce.php')){return;}

class smsalert_delivery_drivers_woocommerce
{
	public function __construct() {
		add_filter('sAlertDefaultSettings',  __CLASS__ .'::addDefaultSetting',1);
		add_filter('sa_wc_variables',  __CLASS__ .'::addTemplateVariable',1,2);

		$smsalert_driver_notify  = smsalert_get_option( 'driver_notify', 'smsalert_driver_general', 'on');

		if($smsalert_driver_notify == 'on'){
			add_action( 'woocommerce_order_status_changed', array( $this, 'trigger_onchange_order_status' ), 10, 3 );
		}
		add_action( 'sa_addTabs', array( $this, 'addTabs' ), 100 );
		add_action( 'ddwc_driver_dashboard_change_status_forms_bottom', array( $this, 'addCodeVerify' ), 100 );
		add_action( 'wp_enqueue_scripts', array($this,'enqueue_script_for_otp_sms'));

		add_filter('sa_wc_order_sms_customer_before_send', __CLASS__ .'::modifySMSTextByOrderId',1,2);

		$this->verify_delivery_code();
	}

	public function addCodeVerify(){

		$order_id     		= filter_input( INPUT_GET, 'orderid' );
		$order        		= wc_get_order( $order_id );
		$order_data   		= $order->get_data();
		$order_status 		= $order_data['status'];

		$phone_number 		= get_post_meta($order_id, $key = '_billing_phone', $single = true );
		$code_verify_enable =  metadata_exists( 'post', $order_id,  '_sa_deliverycode');

		$verify_code_status = get_post_meta($order_id, '_sa_deliverycode_status', true );

		wp_register_script( 'smsalert-auth', SA_MOV_URL . 'js/otp-sms.min.js', array('jquery'), SmsAlertConstants::SA_VERSION, true );

		$code_message 		= str_replace("##phone##",$phone_number,SmsAlertMessages::showMessage('OTP_SENT_PHONE'));
		$invalid_message 	= SmsAlertMessages::showMessage('INVALID_OTP');
		if($order_status == 'out-for-delivery' && $verify_code_status == '0' && !empty($code_verify_enable)){
			echo '<script>
				jQuery(document).ready(function(){
					var button = jQuery("input[name=ordercompleted]");					jQuery("input[name=ordercompleted]").attr("type","hidden");			jQuery("input[name=ordercompleted]").after(button.clone()).addClass("sa-otp-btn-init").html();
					jQuery(".sa-otp-btn-init").attr("type","submit");				jQuery(".sa-otp-btn-init").attr("id","submit_code").attr("name","submit_code");
					jQuery("#submit_code").click(function(e){
						e.preventDefault();
						jQuery(".modal").show();
					});
					var message = "'.$code_message.'";
					jQuery(".sa-message").html(message);
					jQuery(".sa_resend_btn, .sa_timer").hide();					jQuery(".smsalert_otp_validate_submit").click(function(){
						var code = jQuery("#smsalert_customer_validation_otp_token").val();
						var order_id = "'.$_GET["orderid"].'";
						var invalid_message = "'.$invalid_message.'";
						if(code != " "){
							jQuery.ajax({
								url         : "'.site_url().'/?option=sa_verify_delivery_code",
								data        : {verify_code:code,order_id:order_id},
								dataType	: "json",
								type: "post",
								success: function(data)
								{
									if(data.result == "success"){
										jQuery("input[name= ordercompleted]").parent("form").submit();
									}else{
										jQuery(".sa-message").removeClass("woocommerce-message");								jQuery(".sa-message").addClass("woocommerce-error");
										jQuery(".sa-message").html(invalid_message);
									}
								}
							});
						}
						return false;
					});
				});
			</script>';

			$template_style 	= smsalert_get_option( 'otp_template_style', 'smsalert_general', 'otp-popup-1.php');
			echo get_smsalert_template('template/'.$template_style,$params=array());
		}
	}

	function enqueue_script_for_otp_sms()
	{
		wp_register_script( 'smsalert-auth', SA_MOV_URL . 'js/otp-sms.min.js', array('jquery'), SmsAlertConstants::SA_VERSION, true );
		wp_enqueue_script('smsalert-auth');
	}

	public function verify_delivery_code(){
		if(!empty($_REQUEST['option']) && $_REQUEST['option']=='sa_verify_delivery_code'){

			$order_id 		= $_REQUEST['order_id'];
			$delivery_code 	= get_post_meta($order_id, $key = '_sa_deliverycode', $single = true );
			if($_REQUEST['verify_code'] == $delivery_code){
				update_post_meta($order_id, "_sa_deliverycode_status", 1);
				wp_send_json( SmsAlertUtility::_create_json_response("Code Validated Successfully.",'success'));
			}else{
				wp_send_json( SmsAlertUtility::_create_json_response("Invalid Code",'error'));
			}
		}
	}

	/*add tabs to smsalert settings at backend*/
	public static function addTabs($tabs=array())
	{
		$delivery_drivers_param=array(
			'checkTemplateFor'	=> 'delivery_drivers',
			'templates'			=> self::getDeliverDriversTemplates(),
		);

		$tabs['woocommerce']['inner_nav']['delivery']['title']		 = 'Delivery Drivers';
		$tabs['woocommerce']['inner_nav']['delivery']['tab_section'] = 'deliverydriverstemplates';
		$tabs['woocommerce']['inner_nav']['delivery']['tabContent']	 = self::getContentFromTemplate('views/message-template.php',$delivery_drivers_param);
		return $tabs;
	}

	public static function getContentFromTemplate($path,$params=array())
	{
		return get_smsalert_template($path,$params);
	}

	public static function getDeliverDriversTemplates()
	{
		$current_val 		= smsalert_get_option( 'driver_notify', 'smsalert_driver_general', 'on');
		$checkboxNameId		= 'smsalert_driver_general[driver_notify]';
		$textareaNameId		= 'smsalert_driver_message[driver_notify]';

		$text_body 			= smsalert_get_option('driver_notify', 'smsalert_driver_message',
		SmsAlertMessages::showMessage('DEFAULT_DELIVERY_DRIVER_MESSAGE'));

		$templates 			= array();

		$variables = array(
			'[first_name]' 		=> 'First Name',
			'[last_name]' 		=> 'Last Name',
			'[order_id]' 		=> 'Order Id',
			'[item_name_qty]' 	=> 'Product Name with Quantity',
			'[item_name]' 		=> 'Product Name',
			'[store_name]' 		=> 'Store Name',
		);

		$templates['delivery-drivers']['title'] 			= 'When Order is assigned to driver';
		$templates['delivery-drivers']['enabled'] 			= $current_val;
		$templates['delivery-drivers']['status'] 			= 'driver-assigned';
		$templates['delivery-drivers']['text-body'] 		= $text_body;
		$templates['delivery-drivers']['checkboxNameId'] 	= $checkboxNameId;
		$templates['delivery-drivers']['textareaNameId'] 	= $textareaNameId;
		$templates['delivery-drivers']['token'] 			= $variables;

		return $templates;
	}

	public static function modifySMSTextByOrderId($params,$order_id)
	{
		if(empty($params['sms_body']))
			return $params;

		$order 				= new WC_Order( $order_id );
		$order_items 		= $order->get_items();
		$first_item 		= current($order_items);
		$post_id 			= $first_item['order_id'];
		$driver_id 			= get_post_meta( $post_id, 'ddwc_driver_id', true);
		$order_variables 	= get_user_meta($driver_id);
		$delivery_code 		= get_post_meta($post_id, $key = '_sa_deliverycode', $single = true );
		$find = array(
			'[delivery_first_name]',
			'[delivery_last_name]',
			'[delivery_boy_number]',
			'[delivery_code]',
		);

		$replace = array(
			'[first_name]',
			'[last_name]',
			'[billing_phone]',
			$delivery_code,
		);

		$content = str_replace( $find, $replace, $params['sms_body'] );
		if(!empty($order_variables))
		{
			foreach ($order_variables as &$value) {
			$value = $value[0];
			}
			unset($value);

			$order_variables = array_combine(
				array_map(function($key){ return '['.ltrim($key, '_').']'; }, array_keys($order_variables)),
				$order_variables
			);
			$content = str_replace( array_keys($order_variables), array_values($order_variables), $content );

			$params['sms_body'] = $content;
		}
		return $params;
	}

	public static function addTemplateVariable($variables=array(), $status)
	{
		if($status == 'driver-assigned'){
			$variables = array_merge($variables,  array(
				'[delivery_first_name]' => 'Delivery Boy First Name',
				'[delivery_last_name]' 	=> 'Delivery Boy Last Name',
				'[delivery_boy_number]' => 'Delivery Boy Number',
				'[delivery_code]' 		=> 'Delivery Code',
			));
		}
		return $variables;
	}

	/*add default settings to savesetting in setting-options*/
	public static function addDefaultSetting($defaults=array())
	{
		$defaults['smsalert_driver_general']['driver_notify']	= 'off';
		$defaults['smsalert_driver_message']['driver_notify']	= '';
		return $defaults;
	}

	public  function trigger_onchange_order_status( $order_id, $old_status, $new_status ) {

		if($new_status == 'driver-assigned')
		{
			$delivery_code 	= rand(1,9999);

			$order 			= new WC_Order( $order_id );
			$driver_message = smsalert_get_option('driver_notify', 'smsalert_driver_message','');
			$cust_message	= smsalert_get_option( 'sms_body_driver-assigned', 'smsalert_message', '');

			$order_items 	= $order->get_items();
			$first_item 	= current($order_items);
			$post_id 		= $first_item['order_id'];
			$driver_id 		= get_post_meta( $post_id, 'ddwc_driver_id', true);
			$driver_no 		= get_the_author_meta('billing_phone', $driver_id);
			do_action('sa_send_sms', $driver_no, $this->parse_sms_body($order, $driver_message, $driver_id));

			if(strpos($cust_message, "[delivery_code]") !== false){
				$this->saveDeliveryCode($order_id,$delivery_code);
			}
		}
	}

	public function saveDeliveryCode($order_id,$delivery_code){
		update_post_meta($order_id, "_sa_deliverycode", $delivery_code);
		update_post_meta($order_id, "_sa_deliverycode_status", 0);
	}

	public function parse_sms_body($order, $message, $driver_id){

		$order_items 	= $order->get_items();
		$item 			= current($order_items);
		$item_name 		= $item['name'];
		$order_id 		= $item['order_id'];
		$quantity 		= $item['quantity'];
		$first_name 	= get_the_author_meta('first_name', $driver_id);
		$last_name 		= get_the_author_meta('last_name', $driver_id);

		$find = array(
			'[first_name]',
			'[last_name]',
			'[item_name]',
			'[order_id]',
			'[item_name_qty]',
			'[store_name]',
		);

		$replace = array(
			$first_name,
			$last_name,
			$item_name,
			$order_id,
			$item_name.' '.$quantity,
			get_bloginfo(),
		);

		$message 	= str_replace($find, $replace, $message);
		return $message;
	}
}
new smsalert_delivery_drivers_woocommerce;
?>