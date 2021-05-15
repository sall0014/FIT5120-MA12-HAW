<?php
	if (! defined( 'ABSPATH' )) exit;
	if ( 
			is_plugin_active('woocommerce-shipment-tracking/woocommerce-shipment-tracking.php') ||
			is_plugin_active('woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php') ||
			is_plugin_active('ast-tracking-per-order-items/ast-tracking-per-order-items.php')||
			is_plugin_active('aftership-woocommerce-tracking/aftership.php')
	)
	{
		new SAShipmentIntegration;
	}
	class SAShipmentIntegration
	{
		public function __construct() {
			if(is_plugin_active('ast-tracking-per-order-items/ast-tracking-per-order-items.php'))
			{
				add_filter('sAlertDefaultSettings',  __CLASS__ .'::addDefaultSetting',1);
				add_action( 'sa_addTabs', array( $this, 'addTabs' ), 100 );
				
				$smsalert_ast_notify = smsalert_get_option( 'ast_notify', 'smsalert_ast_general', 'on');
				
				if($smsalert_ast_notify== 'on'){
					add_action( 'send_order_to_trackship', array( $this, 'trigger_order_trackship' ), 10, 1 );
				}
				
			}
			
			add_filter( 'sa_wc_order_sms_customer_before_send' , array( $this,'replaceAftShpTrackingNo'),10,2 );
			add_filter( 'sa_wc_order_sms_customer_before_send' , array( $this,'replaceWCShipmentTrackingNo'),10,2 );
			add_filter( 'sa_wc_order_sms_customer_before_send' , array( $this,'replaceWCAdvShipmentTrackingNo'),10,2 );
			
			add_filter( 'sa_wc_variables' , array( $this,'addTokensInWCTemplates'),10,2 );
		}
		
		public function addTokensInWCTemplates($variables,$status)
		{
			if ( is_plugin_active( 'woocommerce-shipment-tracking/woocommerce-shipment-tracking.php' ) ||
			is_plugin_active( 'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php')
			)
			{
				$wc_shipment_variables = array(
					'[tracking_number]' 		=> 'Tracking Number',
					'[tracking_provider]' 		=> 'Tracking Provider',
					'[tracking_link]' 			=> 'Tracking Link',
				);
				$variables = array_merge($variables, $wc_shipment_variables);
			}
			if ( is_plugin_active( 'aftership-woocommerce-tracking/aftership.php' ) )
			{
				$wc_shipment_variables = array(
					'[aftership_tracking_number]' 			=> 'afshp tracking number',
					'[aftership_tracking_provider_name]' 	=> 'afshp tracking provider',
					//'[tracking_link]' 			=> 'tracking link',
				);
				$variables = array_merge($variables, $wc_shipment_variables);
			}
			
			return $variables;
		}
		
		public function replaceWCShipmentTrackingNo($sms_data,$order_id)
		{
			if(is_plugin_active('woocommerce-shipment-tracking/woocommerce-shipment-tracking.php'))
			{
				$content = (!empty($sms_data['sms_body'])) ? $sms_data['sms_body'] : '';
				if((strpos($content, '[tracking_number]')!== false)||(strpos($content, '[tracking_provider]')!== false) || (strpos($content, '[tracking_link]')!== false))
				{
					
					$tracking_info              = get_post_meta( $order_id, '_wc_shipment_tracking_items', true );
					if(sizeof($tracking_info) > 0)
					{
						$t_info 				= array_shift($tracking_info);
						$find = array('[tracking_number]','[tracking_provider]','[tracking_link]',);
						$replace = array(
						$t_info['tracking_number'],
						(($t_info['tracking_provider'] != '') ? $t_info['tracking_provider'] : $t_info['custom_tracking_provider']),
						$t_info['custom_tracking_link'],
						);
						
						$sms_data['sms_body'] = str_replace( $find, $replace, $content );
						
					}
				}
			}
			return $sms_data;
		}
		
		
		public function replaceWCAdvShipmentTrackingNo($sms_data,$order_id)
		{
			if(is_plugin_active('woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php'))
			{
				$content = (!empty($sms_data['sms_body'])) ? $sms_data['sms_body'] : '';
				
				
				if((strpos($content, '[tracking_number]')!== false)||(strpos($content, '[tracking_provider]')!== false) || (strpos($content, '[tracking_link]')!== false))
				{
					$ast                        = new WC_Advanced_Shipment_Tracking_Actions;
					$tracking_items             = $ast->get_tracking_items( $order_id,true );
					if (count($tracking_items)>0)
					{
						$t_info 				= end($tracking_items);
						$find = array(
						'[tracking_number]',
						'[tracking_provider]',
						'[tracking_link]'
						);
						$replace = array(
						$t_info['tracking_number'],
						$t_info['formatted_tracking_provider'],
						$t_info['formatted_tracking_link']
						);
						
						$sms_data['sms_body'] = str_replace( $find, $replace, $content );
					}
					
				}
			}
			return $sms_data;
		}
		
		
		public function replaceAftShpTrackingNo($sms_data,$order_id)
		{
			if(is_plugin_active( 'aftership-woocommerce-tracking/aftership.php'))
			{
				$content = (!empty($sms_data['sms_body'])) ? $sms_data['sms_body'] : '';
				if((strpos($content, '[aftership_tracking_number]')!== false) || (strpos($content, '[aftership_tracking_provider_name]')!== false))
				{	
					$find = array(
					'[aftership_tracking_number]',
					'[aftership_tracking_provider_name]'
					);
					$replace = array(
					get_post_meta( $order_id, '_aftership_tracking_number', true ),
					get_post_meta( $order_id, '_aftership_tracking_provider_name', true ),
					);
					
					$sms_data['sms_body'] = str_replace( $find, $replace, $content );
				}
			}
			return $sms_data;
		}
		
		/*add default settings to savesetting in setting-options*/
		public static function addDefaultSetting($defaults                =array())
		{
			$defaults['smsalert_ast_general']['ast_notify']	              = 'off';
			$defaults['smsalert_ast_message']['ast_notify']	              = '';
			return $defaults;
		}
		
		/*add tabs to smsalert settings at backend*/
		public static function addTabs($tabs                              =array())
		{
			$ast_addon__param                                             =array(
			'checkTemplateFor'	                                          => 'ast_addon',
			'templates'			                                          => self::getASTAddonTemplates(),
			);
			
			$tabs['woocommerce']['inner_nav']['ast_addon']['title']		  = 'AST Tracking Per Item';
			$tabs['woocommerce']['inner_nav']['ast_addon']['tab_section'] = 'astaddontemplates';
			$tabs['woocommerce']['inner_nav']['ast_addon']['tabContent']  = self::getContentFromTemplate('views/message-template.php',$ast_addon__param);
			return $tabs;
		}
		
		public static function getContentFromTemplate($path,$params       =array())
		{
			return get_smsalert_template($path,$params);
		}
		
		public static function getASTAddonTemplates()
		{
			$current_val 		                                          = smsalert_get_option( 'ast_notify', 'smsalert_ast_general', 'on');
			$checkboxNameId		                                          = 'smsalert_ast_general[ast_notify]';
			$textareaNameId		                                          = 'smsalert_ast_message[ast_notify]';
			
			$text_body 			                                          = smsalert_get_option('ast_notify', 'smsalert_ast_message', sprintf(__('Shipped: %s has been dispatched via %s with tracking number %s. Track here %s','sms-alert'), '[item_name_qty]', '[tracking_provider]', '[tracking_number]', '[tracking_link]'));
			
			$templates 			                                          = array();
			
			$variables                                                    = WooCommerceCheckOutForm::getvariables();
			
			$templates['ast-addon']['title'] 			                  = 'When Tracking Information is added';
			$templates['ast-addon']['enabled'] 			                  = $current_val;
			$templates['ast-addon']['status'] 			                  = '';
			$templates['ast-addon']['text-body'] 		                  = $text_body;
			$templates['ast-addon']['checkboxNameId'] 	                  = $checkboxNameId;
			$templates['ast-addon']['textareaNameId'] 	                  = $textareaNameId;
			$templates['ast-addon']['moreoption'] 		                  = 1;
			$templates['ast-addon']['token'] 			                  = $variables;
			
			return $templates;
		}
		
		public function trigger_order_trackship( $order_id) {
			
			$order 			                                              = new WC_Order( $order_id );
			$ast_message 	                                              = smsalert_get_option('ast_notify', 'smsalert_ast_message','');
			
			$order_items 	                                              = $order->get_items();
			$first_item 	                                              = current($order_items);
			$post_id 		                                              = $first_item['order_id'];
			$buyer_no 		                                              = get_post_meta($post_id, '_billing_phone', true);
			
			do_action('sa_send_sms', $buyer_no, $this->parse_sms_body($ast_message, $order, $order_id));
		}
		
		public function parse_sms_body($content, $order, $order_id)
		{
			$ast 			                                              = new WC_Advanced_Shipment_Tracking_Actions;
			$tracking_items                                               = $ast->get_tracking_items( $order_id,true );
			
			if(count($tracking_items)>0)
			{
				$t_info 	                                              = end($tracking_items);
				if(array_key_exists('products_list',$t_info)){
					$item_with_qty 	                                      = array();
					$item_name 		                                      = array();
					foreach($t_info['products_list'] as $pdata){
						$item_with_qty[]                                  = get_the_title ($pdata->product)." [".$pdata->qty."] ";
						$item_name[]	                                  = get_the_title ($pdata->product);
					}
					$item_with_qty 	                                      = implode(",", $item_with_qty);
					$item_name 		                                      = implode(",", $item_name);
				}
			}
			
			$find                                                         = array(
			'[item_name]',
			'[item_name_qty]',
			);
			
			$replace                                                      = array(
			$item_name,
			$item_with_qty,
			);
			
			$content                                                      = str_replace( $find, $replace, $content );
			$buyer_sms_data['sms_body']                                   = $content; 
			$buyer_sms_data = $this->replaceWCAdvShipmentTrackingNo($buyer_sms_data,$order_id);
			$content 					     = ((!empty($buyer_sms_data['sms_body'])) ? $buyer_sms_data['sms_body'] : '');
			return $content;
		}
	}
	
	/*******SAWCInvoicePdf********/	
	if (is_plugin_active('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php' ) ){new SAWCInvoicePdf;}
	
	class SAWCInvoicePdf
	{
		public function __construct() {
			add_filter( 'sa_wc_order_sms_customer_before_send' , array( $this,'replaceTokenInWCTemplates'),10,2 );
			add_filter( 'sa_wc_variables' , array( $this,'addTokensInWCTemplates'),10,2 );
		}
		
		public function addTokensInWCTemplates($variables,$status)
		{
			if ( is_plugin_active('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php' ) )
			{
				$variables = array_merge($variables, array(
					'[pdf_invoice_link]' 		=> 'pdf invoice link',
				));
			}
			return $variables;
		}
		
		public function replaceTokenInWCTemplates($sms_data,$order_id)
		{
			if ( is_plugin_active('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php' ) )
			{
				$order 								= new WC_Order($order_id);
				$sms_data['sms_body'] =str_replace( "[pdf_invoice_link]", admin_url( "admin-ajax.php?action=generate_wpo_wcpdf&document_type=invoice&order_ids=" . $order_id."&order_key=".$order->get_order_key() ), $sms_data['sms_body']);
			}
			return $sms_data;
		}
	}
	
	/*******SAWCOrderDeliveryDt********/
	if (is_plugin_active('order-delivery-date-for-woocommerce/order_delivery_date.php' ) ){new SAWCOrderDeliveryDt;}
	class SAWCOrderDeliveryDt
	{
		public function __construct() {
			add_filter( 'sa_wc_order_sms_customer_before_send' , array( $this,'replaceTokenInWCTemplates'),10,2 );
			add_filter( 'sa_wc_variables' , array( $this,'addTokensInWCTemplates'),10,2 );
		}
		
		public function addTokensInWCTemplates($variables,$status)
		{
			if ( is_plugin_active('order-delivery-date-for-woocommerce/order_delivery_date.php' ) )
			{
				$variables = array_merge($variables, array(
					'[orddd_lite_timestamp]' => 'Delivery Date',
				));
			}
			return $variables;
		}
		
		public function replaceTokenInWCTemplates($sms_data,$order_id)
		{
			if ( is_plugin_active('order-delivery-date-for-woocommerce/order_delivery_date.php' ) )
			{
				$sms_data['sms_body'] =str_replace( "[orddd_lite_timestamp]",Orddd_Lite_Common::orddd_lite_get_order_delivery_date($order_id),$sms_data['sms_body']);
			}
			return $sms_data;
		}
	}
	
	/*******SAWCSerialNos********/
	if (is_plugin_active('wc-serial-numbers/wc-serial-numbers.php' ) ){new SAWCSerialNos;}
	class SAWCSerialNos
	{
		public function __construct() {
			add_filter( 'sa_wc_order_sms_customer_before_send' , array( $this,'replaceTokenInWCTemplates'),10,2 );
			add_filter( 'sa_wc_variables' , array( $this,'addTokensInWCTemplates'),10,2 );
		}
		
		public function addTokensInWCTemplates($variables,$status)
		{
			if ( is_plugin_active('wc-serial-numbers/wc-serial-numbers.php' ) )
			{
				$variables = array_merge($variables, array(
					'[wc_serial_no]' => 'WC Serial No.',
				));
			}
			return $variables;
		}
		
		public function replaceTokenInWCTemplates($sms_data,$order_id)
		{
			if ( is_plugin_active('wc-serial-numbers/wc-serial-numbers.php' ) )
			{
				$order 								= new WC_Order($order_id);
				$wc_serial_nos 						= array();
				
				$serial_numbers = WC_Serial_Numbers_Query::init()->from( 'serial_numbers' )->where( 'order_id', intval( $order->get_id() ) )->get();
				foreach($serial_numbers as $serial_number)
				{
					$wc_serial_nos[] = wc_serial_numbers_decrypt_key( $serial_number->serial_key );
				}
				
				$sms_data['sms_body'] =str_replace( "[wc_serial_no]",implode(",",$wc_serial_nos),$sms_data['sms_body']);
			}
			return $sms_data;
		}
	}
	
	/*******SAWCAuctions********/	
	if ( is_plugin_active('woocommerce-simple-auctions/woocommerce-simple-auctions.php' ) ){new SAWCAuctions;}
	class SAWCAuctions
	{
		public function __construct() 
		{
			add_filter( 'sa_wc_order_sms_customer_before_send',array( $this,'replaceTokenInWCTemplates'),10,2 );
			add_filter( 'sa_wc_variables' , array( $this,'addTokensInWCTemplates'),10,2 );
			add_action( 'woocommerce_simple_auctions_outbid',array($this,'send_sms_outbidder'),10,1);
			add_action( 'woocommerce_simple_auctions_place_bid',array($this,'send_sms_bidder'),10,1);
			add_action( 'woocommerce_simple_auctions_place_bid',array($this,'send_admin_sms_on_placebid'),10,1);
			add_filter('sAlertDefaultSettings', __CLASS__ .'::addDefaultSetting',1);
			add_action( 'sa_addTabs', array( $this, 'addTabs' ),100 );
		}
		
		/*add default settings to savesetting in setting-options*/
		public function addDefaultSetting($defaults=array())
		{
			$defaults['smsalert_wcauction_general']['wcauction_admin_notification_new']		        = 'off';
			$defaults['smsalert_wcauction_general']['wcauction_bidder_notification_outbid']	        = 'off';
			$defaults['smsalert_wcauction_general']['wcauction_bidder_notification_customerbid']	= 'off';
			$defaults['smsalert_wcauction_message']['wcauction_admin_sms_body_new']		            = '';
			$defaults['smsalert_wcauction_message']['wcauction_sms_body_outbid']			        = '';
			$defaults['smsalert_wcauction_message']['wcauction_sms_body_customerbid']			    = '';
			return $defaults;
		
		}
		
		/*add tabs to smsalert settings at backend*/
		public static function addTabs($tabs=array())
		{
			$customer_param                                                             =array(
			'checkTemplateFor'	                                                        => 'sa_wc_auction',
			'templates'			                                                        => self::getCustomerTemplates(),
			);

			$admin_param                                                                =array(
				'checkTemplateFor'	                                                    =>'sa_wc_auction_admin',
				'templates'			                                                    =>self::getAdminTemplates(),
			);
			
			$tabs['sa_wc_auction']['nav']			                                    = 'Woo Product Auction';
			$tabs['sa_wc_auction']['icon']			                                    = 'dashicons-admin-users';

			$tabs['sa_wc_auction']['inner_nav']['wc_auction_customer']['title']	        = 'Customer Notifications';
			$tabs['sa_wc_auction']['inner_nav']['wc_auction_customer']['tab_section']   = 'wcauctioncsttemplates';
			$tabs['sa_wc_auction']['inner_nav']['wc_auction_customer']['first_active']  = true;
			$tabs['sa_wc_auction']['inner_nav']['wc_auction_customer']['tabContent']    = self::getContentFromTemplate('views/message-template.php',$customer_param);

			$tabs['sa_wc_auction']['inner_nav']['wc_auction_admin']['title']			= 'Admin Notifications';
			$tabs['sa_wc_auction']['inner_nav']['wc_auction_admin']['tab_section'] 	    = 'wcauctionadmintemplates';
			$tabs['sa_wc_auction']['inner_nav']['wc_auction_admin']['tabContent']		= self::getContentFromTemplate('views/message-template.php',$admin_param);
			return $tabs;
		}
		
		public static function getContentFromTemplate($path,$params=array())
		{
			return get_smsalert_template($path,$params);
		}
		
		public static function getvariables()
		{
			 $variables = array(
				'[auction_id]' 		=> 'Auction Id',
				'[store_name]' 		=> 'Store Name',
				'[first_name]' 		=> 'First Name',
				'[last_name]' 		=> 'Last Name',
				'[auction_name]' 	=> 'Auction Name',
				'[auction_bid]' 	=> 'Auction bid',
				'[auction_link]' 	=> 'Auction link',
			);
			return $variables;
		}
		
		public static function getCustomerTemplates()
		{
			$templates 			                            = array();
			$templates['outbid']['title'] 			        = 'Send SMS to Outbidder';
			$templates['outbid']['enabled'] 		        = smsalert_get_option( 'wcauction_bidder_notification_outbid', 'smsalert_wcauction_general', 'on');
			$templates['outbid']['status'] 			        = 'outbid';
			$templates['outbid']['text-body'] 		        = smsalert_get_option('wcauction_sms_body_outbid', 'smsalert_wcauction_message', sprintf(__('Hello %s, a new bid for auction %s has just been submitted. The new bid is: %s. Please visit the auction %s','sms-alert'), '[first_name]', '[auction_name]', '[auction_bid]', '[auction_link]'));
			$templates['outbid']['checkboxNameId'] 	        = 'smsalert_wcauction_general[wcauction_bidder_notification_outbid]';
			$templates['outbid']['textareaNameId'] 	        = 'smsalert_wcauction_message[wcauction_sms_body_outbid]';
			$templates['outbid']['token'] 			        = self::getvariables();
			/*Send SMS to Bidder*/
			$templates['customerbid']['title'] 			    = 'Send SMS to Bidder';
			$templates['customerbid']['enabled'] 		    = smsalert_get_option( 'wcauction_bidder_notification_customerbid', 'smsalert_wcauction_general', 'on');
			$templates['customerbid']['status'] 			= 'customerbid';
			$templates['customerbid']['text-body'] 		    = smsalert_get_option('wcauction_sms_body_customerbid', 'smsalert_wcauction_message', sprintf(__('Hello %s, Thank You for placing bid for %s. Your bid is %s. Please visit the auction %s','sms-alert'), '[first_name]', '[auction_name]', '[auction_bid]', '[auction_link]'));
			$templates['customerbid']['checkboxNameId'] 	= 'smsalert_wcauction_general[wcauction_bidder_notification_customerbid]';
			$templates['customerbid']['textareaNameId'] 	= 'smsalert_wcauction_message[wcauction_sms_body_customerbid]';
			$templates['customerbid']['token'] 			    = self::getvariables();
			
			return $templates;
		}
		
		public static function getAdminTemplates()
		{
			$templates 		                    = array();
			$ks 			                    = 'new';
			$current_val 	                    = smsalert_get_option( 'wcauction_admin_notification_new', 'smsalert_wcauction_general', 'on');

			$checkboxNameId	                    = 'smsalert_wcauction_general[wcauction_admin_notification_new]';
			$textareaNameId	                    = 'smsalert_wcauction_message[wcauction_admin_sms_body_new]';

			$text_body 		                    = smsalert_get_option('wcauction_admin_sms_body_new', 'smsalert_wcauction_message', sprintf(__('%s a new bid for auction %s has been submitted by %s. The new bid is: %s. Please visit the auction %s','sms-alert'), '[store_name]:', '[auction_name]', '[first_name]', '[auction_bid]', '[auction_link]'));
			
			$templates[$ks]['title'] 			= 'When Auction is new';
			$templates[$ks]['enabled'] 			= $current_val;
			$templates[$ks]['status'] 			= $ks;
			$templates[$ks]['text-body'] 		= $text_body;
			$templates[$ks]['checkboxNameId'] 	= $checkboxNameId;
			$templates[$ks]['textareaNameId'] 	= $textareaNameId;
			$templates[$ks]['token'] 			= self::getvariables();
			return $templates;
		}
		
		function send_sms_outbidder($datas=array())
		{
			
			$outbid                  = smsalert_get_option( 'wcauction_bidder_notification_outbid', 'smsalert_wcauction_general');
			$message                 = smsalert_get_option( 'wcauction_sms_body_outbid', 'smsalert_wcauction_message');
			
			if($outbid=='on' && $message!='')
			{
				$product_id          = $datas['product_id'];
				$product_data        = wc_get_product($product_id);
				$outbiddeduser_id    = $datas['outbiddeduser_id'];
				$currentBidderId     = $product_data->get_auction_current_bider();
				
				if($outbiddeduser_id == $currentBidderId){
					return;
				}
				
				$outbider_phone 	 = get_user_meta($outbiddeduser_id,'billing_phone',true);
				do_action('sa_send_sms', $outbider_phone, $this->replaceTokenInWCTemplates($message,$product_id,$outbiddeduser_id));
			}
		}
		
		function send_sms_bidder($datas=array())
		{
			$customerbid            = smsalert_get_option( 'wcauction_bidder_notification_customerbid', 'smsalert_wcauction_general');
			$message                = smsalert_get_option( 'wcauction_sms_body_customerbid', 'smsalert_wcauction_message');
			
			if($customerbid         =='on' && $message!='')
			{
				$product_id         = $datas['product_id'];
				$product_data       = wc_get_product($product_id);
				$currentBidderId    = $product_data->get_auction_current_bider();
				
				
				$cur_bidder_phone 	= get_user_meta($currentBidderId,'billing_phone',true);
				do_action('sa_send_sms', $cur_bidder_phone, $this->replaceTokenInWCTemplates($message,$product_id,$currentBidderId));
			}
		}
		
		function send_admin_sms_on_placebid($datas=array())
		{
			$admin_outbid               = smsalert_get_option( 'wcauction_admin_notification_new', 'smsalert_wcauction_general');
			$admin_sms_content          = smsalert_get_option( 'wcauction_admin_sms_body_new', 'smsalert_wcauction_message');
			
			$admin_phone_number         = smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );
			$admin_phone_number 	    = str_replace('postauthor','post_author',$admin_phone_number);
			
			if($admin_outbid=='on' && $admin_phone_number!='' && $admin_sms_content!='')
			{
				$admin_phone_number 	= str_replace('post_author','',$admin_phone_number);
				$product_id             = $datas['product_id'];
				$product_data     		= wc_get_product($product_id);
				$currentBidderId  		= $product_data->get_auction_current_bider();
				do_action('sa_send_sms', $admin_phone_number, $this->replaceTokenInWCTemplates($admin_sms_content,$product_id,$currentBidderId));
			}
		}
		
		public function replaceTokenInWCTemplates($message,$product_id,$user_id)
		{
			$product_data           = wc_get_product($product_id);
			$first_name 	        = get_user_meta($user_id,'billing_first_name',true);
			$last_name 	            = get_user_meta($user_id,'billing_last_name',true);
			
			$replace                = array(
				'[auction_id]' 		=> $product_id,
				'[store_name]' 		=> get_bloginfo(),
				'[first_name]' 		=> $first_name,
				'[last_name]' 		=> $last_name,
				'[auction_name]' 	=> $product_data->get_title(),
				'[auction_bid]' 	=> $product_data->get_curent_bid(),
				'[auction_link]' 	=> get_permalink($product_id),
			);
			
			$message                = str_replace( array_keys($this->getvariables()),array_values($replace),$message);
			return $message;
		}
	}
	
	
	
		
?>