<?php
	if (! defined( 'ABSPATH' )) exit;
	if (!is_plugin_active( 'woocommerce-warranty/woocommerce-warranty.php' ) ){return;}
	class sa_Return_Warranty
	{
		public function __construct() {
			add_filter( 'sAlertDefaultSettings',  __CLASS__ .'::addDefaultSetting',1);
			add_action( 'sa_addTabs', array( $this, 'addTabs' ), 10 );
			add_action( 'wc_warranty_settings_tabs', __CLASS__ .'::smsalert_warranty_tab'  );
			add_action( 'wc_warranty_settings_panels', __CLASS__ .'::smsalert_warranty_settings_panels'  );
			add_action( 'admin_post_wc_warranty_settings_update', array($this, 'update_wc_warranty_settings'),5 );
			add_action( 'wp_ajax_warranty_update_request_fragment', array($this, 'on_rma_status_update'),0 );
			add_action( 'wc_warranty_created',  array($this, 'on_new_rma_request'),5);
		}
		
		public static function getWarrantStatus()
		{
			if (!class_exists('WooCommerce_Warranty')) {
				return array();
			}
			
			$wc_warranty                                                = new WooCommerce_Warranty();
			return $wc_warranty->get_default_statuses();
		}
		
		function update_wc_warranty_settings($data)
		{
			$options                                                    = $_POST;
			if($options['tab']== 'smsalert_warranty')
			{
				foreach($options as $name=> $value)
				{
					if(is_array($value))
					{
						foreach($value as $k=> $v)
						{
							if(!is_array($v))
							{
								$value[$k]                              = stripcslashes($v);
							}
						}
					}
					update_option( $name, $value );
				}
			}
		}
		
		function send_rma_status_sms($request_id,$status)
		{
			$wc_warranty_checkbox                                       =smsalert_get_option('warranty_status_'.$status, 'smsalert_warranty','');
			$is_sms_enabled 	                                        = ($wc_warranty_checkbox=='on')  ? true : false;
			if($is_sms_enabled)
			{
				$sms_content	                                        = smsalert_get_option('sms_text_'.$status, 'smsalert_warranty','');
				$order_id 		                                        = get_post_meta( $request_id, '_order_id', true );
				$rma_id 		                                        = get_post_meta( $request_id, '_code', true );
				$order 			                                        = wc_get_order( $order_id );
				global $wpdb;
				$products 		                                        = $items = $wpdb->get_results( $wpdb->prepare(
				"SELECT *
				FROM {$wpdb->prefix}wc_warranty_products
				WHERE request_id                                        = %d",
				$request_id
				), ARRAY_A );
				
				$item_name 		                                        = '';
				foreach ( $products as $product ) {
					
					if ( empty( $product['product_id'] ) && empty( $item['product_name'] ) ) {
						continue;
					}
					
					if ( $product['product_id']== 0 ) {
						$item_name .= $item['product_name'].', ';
						} else {
						$item_name .= warranty_get_product_title( $product['product_id'] ).', ';
					}
				}
				
				$item_name 					                            = rtrim($item_name, ', ');
				$sms_content 				                            = str_replace( '[item_name]', $item_name, $sms_content );
				$buyer_sms_data				                            = array();
				$buyer_mob   				                            = get_post_meta( $order_id, '_billing_phone', true );
				$buyer_sms_data['number'] 	                            = $buyer_mob; 
				$buyer_sms_data['sms_body']                             = $sms_content; 
				$buyer_sms_data['rma_id'] 	                            = $rma_id; 
				$buyer_sms_data 			                            = WooCommerceCheckOutForm::pharse_sms_body($buyer_sms_data,$order_id);
				$message 					                            = (!empty($buyer_sms_data['sms_body'])) ? $buyer_sms_data['sms_body'] : '';
				
				do_action('sa_send_sms', $buyer_mob, $message);
			}
		}
		
		function on_new_rma_request($warranty_id)
		{
			$this->send_rma_status_sms($warranty_id,"new");
		}
		
		function on_rma_status_update()
		{
			$request_id                                                 = $_POST['request_id'];
			$status 	                                                = $_POST['status'];
			
			$this->send_rma_status_sms($request_id,$status);
		}
		
		public static function smsalert_warranty_tab()
		{
			$active_tab                                                 = isset($_GET['tab'])?$_GET['tab']:'';
		?>
		<a href                                                         ="admin.php?page=warranties-settings&tab=smsalert_warranty" class="nav-tab <?php echo ($active_tab == 'smsalert_warranty') ? 'nav-tab-active' : ''; ?>"><?php _e('SMS Alert', 'wc_warranty'); ?></a>
		<?php
		}
		
		public static function smsalert_warranty_settings_panels()
		{
			$active_tab	                                                = isset($_GET['tab'])?$_GET['tab']:'';
			
			if($active_tab                                              == 'smsalert_warranty')
			{
				$return_warranty_param                                  =array(
				'checkTemplateFor'	                                    => 'return_warranty',
				'templates'			                                    => self::getReturnWarrantyTemplates(),
				);
				echo get_smsalert_template('views/message-template.php',$return_warranty_param);
			}
		}
		
		/*add default settings to savesetting in setting-options*/
		public function addDefaultSetting($defaults=array())
		{
			$wc_warrant_status 	                                        = self::getWarrantStatus();
			
			foreach($wc_warrant_status as $ks                           => $vs)
			{
				$vs 					                                = str_replace(' ', '-', strtolower($vs));			
				$defaults['smsalert_warranty']['warranty_status_'.$vs]	= 'off';
				$defaults['smsalert_warranty']['sms_text_'][$vs] 		= '';
			}
			return $defaults;
		}
		
		/*add tabs to smsalert settings at backend*/
		public static function addTabs($tabs                            =array())
		{
			$return_warranty_param                                      =array(
			'checkTemplateFor'	                                        => 'return_warranty',
			'templates'			                                        => self::getReturnWarrantyTemplates(),
			);
			
			$tabs['return_warranty']['title']		                    = __("Return & Warranty",'sms-alert');
			$tabs['return_warranty']['tab_section']	                    = 'return_warranty';
			$tabs['return_warranty']['tabContent']	                    = self::getContentFromTemplate('views/message-template.php',$return_warranty_param);
			$tabs['return_warranty']['icon']		                    = 'dashicons-products';
			return $tabs;
		}
		
		public static function getContentFromTemplate($path,$params     =array())
		{
			return get_smsalert_template($path,$params);
		}
		
		public static function getReturnWarrantyTemplates()
		{
			$wc_warrant_status 	                                        = self::getWarrantStatus();
			$variables                                                  = array(
			'[order_id]' 			                                    => 'Order Id',
			'[rma_number]' 			                                    => 'RMA Number',
			'[rma_status]' 			                                    => 'RMA Status',
			'[order_amount]' 		                                    => 'Order Total',
			'[billing_first_name]' 	                                    => 'First Name',
			'[item_name]' 			                                    => 'Product Name',
			'[store_name]' 			                                    => 'Store Name',
			);
			$templates 			                                        = array();
			
			foreach($wc_warrant_status as $ks                           => $vs){
				
				$vs 				                                    = str_replace(' ', '-', strtolower($vs));
				$wc_warranty_text 	                                    = smsalert_get_option('sms_text_'.$vs, 'smsalert_warranty','');
				$current_val 		                                    = smsalert_get_option('warranty_status_'.$vs, 'smsalert_warranty','on');
				
				$checkboxNameId		                                    = 'smsalert_warranty[warranty_status_'.$vs.']';
				$textareaNameId		                                    = 'smsalert_warranty[sms_text_'.$vs.']';
				
				$text_body 			                                    = smsalert_get_option('sms_text_'.$vs, 'smsalert_warranty', '') ? smsalert_get_option('sms_text_'.$vs, 'smsalert_warranty', '') : SmsAlertMessages::showMessage('DEFAULT_WARRANTY_STATUS_CHANGED');
				
				$templates[$ks]['title'] 			                    = 'When RMA is '.ucwords($vs);
				$templates[$ks]['enabled'] 			                    = $current_val;
				$templates[$ks]['status'] 			                    = $ks;
				$templates[$ks]['text-body'] 		                    = $text_body;
				$templates[$ks]['checkboxNameId'] 	                    = $checkboxNameId;
				$templates[$ks]['textareaNameId'] 	                    = $textareaNameId;
				$templates[$ks]['token'] 			                    = $variables;
			}
			return $templates;
		}
	}
	new sa_Return_Warranty;
?>