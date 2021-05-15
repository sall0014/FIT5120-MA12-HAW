<?php
if (! defined( 'ABSPATH' )) exit;
if (!is_plugin_active( 'woocommerce/woocommerce.php' ) ) { return; }
class WooCommerceCheckOutForm extends FormInterface
{
	private $guestCheckOutOnly;
	private $showButton;
	private $formSessionVar  = FormSessionVars::WC_CHECKOUT;
	private $formSessionVar2 = 'block-checkout';
	private $formSessionVar3 = 'post-checkout';
	private $popupEnabled;
	private $paymentMethods;
	private $otp_for_selected_gateways;

	function handleForm()
	{
		add_filter( 'woocommerce_checkout_fields' , array( $this,'get_checkout_fields'),1,1 );
		add_filter( 'default_checkout_billing_phone' , array( $this,'modify_billing_phone_field'),1,2 );
		
		$post_verification = smsalert_get_option('post_order_verification', 'smsalert_general');
		if($post_verification=='on'){add_action('woocommerce_thankyou_order_received_text', array( $this,'send_post_order_otp'),10,2);}
		
		add_action( 'woocommerce_blocks_enqueue_checkout_block_scripts_after', array($this,'showButtonOnBlockPage'));
		add_filter('woocommerce_registration_errors', array($this,'woocommerce_site_registration_errors'),10,3);

		$this->paymentMethods 				= maybe_unserialize(smsalert_get_option( 'checkout_payment_plans', 'smsalert_general' ));
		$this->otp_for_selected_gateways 	= (smsalert_get_option('otp_for_selected_gateways', 'smsalert_general')=="on") ? TRUE : FALSE;
		$this->popupEnabled					= (smsalert_get_option('checkout_otp_popup', 'smsalert_general')=="on") ? TRUE : FALSE;
		$this->guestCheckOutOnly			= (smsalert_get_option('checkout_show_otp_guest_only', 'smsalert_general')=="on") ? TRUE : FALSE;
		$this->showButton 					= (smsalert_get_option('checkout_show_otp_button', 'smsalert_general')=="on") ? TRUE : FALSE;
		
		if($post_verification!="on")
		{
			add_action( 'woocommerce_checkout_process', array($this,'my_custom_checkout_field_process'));
			if($this->popupEnabled)
			{
				add_action( 'woocommerce_review_order_before_submit' , array($this,'add_custom_popup') 		, 99	);
				//add_action( 'woocommerce_review_order_after_submit'  , array($this,'add_custom_button')		, 1, 1	);//23-12-2020
				
				add_action( 'woocommerce_review_order_after_submit'  , array($this,'hideShowBTN')		, 1, 1	);
				add_action( 'woocommerce_review_order_before_submit'  , array($this,'add_custom_button')		, 1, 1	);
			}
			else
			{
				add_action( 'woocommerce_after_checkout_billing_form' , array($this,'my_custom_checkout_field'), 99		);
			}
		}
		add_action( 'wp_enqueue_scripts', array($this,'enqueue_script_on_page'));
		$this->routeData();
	}
	
	//added on 23-12-2020 to autocomplete and changes in pincode fields
	function hideShowBTN()
	{
		if($this->guestCheckOutOnly && is_user_logged_in())  return;
		echo ($this->otp_for_selected_gateways && $this->popupEnabled) ? '' : '<script>jQuery("input[name=woocommerce_checkout_place_order], button[name=woocommerce_checkout_place_order]").hide();</script>';
	}
	
	//onpage load when customer logged in billing phone at checkout page when country code is enabled
	function get_checkout_fields($fields)
	{
		if(!empty($_POST['billing_phone']))
		{
			$_POST['billing_phone'] = SmsAlertUtility::formatNumberForCountryCode($_POST['billing_phone']);
		}
		return $fields;
	}
	
	//onpage modify billing phone at checkout page when country code is enabled
	function modify_billing_phone_field($value,$input)
	{
		if($input=='billing_phone' && !empty($value))
		{
			return SmsAlertUtility::formatNumberForCountryCode($value);
		}
	}

	function woocommerce_site_registration_errors($errors,$username,$email)
	{
		if(smsalert_get_option('allow_multiple_user', 'smsalert_general')!="on" && !SmsAlertUtility::isBlank( $_POST['billing_phone']) )
		{
			$getusers = SmsAlertUtility::getUsersByPhone('billing_phone',$_POST['billing_phone']);
			if(sizeof($getusers) > 0 )
			{
				return new WP_Error('registration-error-number-exists',__( 'An account is already registered with this mobile number. Please login.', 'sms-alert' ));
			}
		}
		return $errors;
	}


	public function showButtonOnBlockPage(){
		$otp_verify_btn_text = smsalert_get_option( 'otp_verify_btn_text', 'smsalert_general', '');
		$otp_resend_timer = smsalert_get_option( 'otp_resend_timer', 'smsalert_general', '15');

		echo '<script>
		jQuery(document).ready(function(){

			var button_text = "'.$otp_verify_btn_text.'";

			var button=jQuery("<button/>").attr({
				type: "button",
				id: "smsalert_otp_token_block_submit",
				title:"Please Enter a Phone Number to enable this link",
				class:"components-button wc-block-components-button button alt ",

			});
			jQuery(".wc-block-components-checkout-place-order-button").next().append(button);
			jQuery(button).insertAfter(".wc-block-components-checkout-place-order-button");
			jQuery(".wc-block-components-checkout-place-order-button").hide();
			jQuery("#smsalert_otp_token_block_submit").text(button_text);
			jQuery("#smsalert_otp_token_block_submit").click(function(){
				var e = jQuery(".wc-block-components-checkout-form").find("#email").val();
				var m = jQuery(".wc-block-components-checkout-form").find("#phone").val();

				saInitBlockOTPProcess(
					this,
					"'.site_url().'/?option=smsalert-woocommerce-block-checkout",
					{user_email:e, user_phone:m},
					'.$otp_resend_timer.',
					function(resp){
						if(resp.result=="success"){$sa(".blockUI").hide()}else{$sa(".blockUI").hide()}
					},
					function(resp){

					}
				)
			});

			jQuery(document).on("click", ".smsalert_otp_validate_submit",function(){
				var current_form = jQuery(".smsalertModal");
				var action_url = "'.site_url().'/?option=smsalert-woocommerce-validate-otp-form";
				var otp_token = jQuery("#order_verify").val();
				var bil_phone = jQuery(".wc-block-components-checkout-form").find("#phone").val();

				var data = {otp_type:"phone",from_both:"",billing_phone:bil_phone,order_verify:otp_token};
				sa_validateBlockOTP(this,action_url,data,function(o){
					jQuery(".wc-block-components-checkout-place-order-button").trigger("click");
				});
				return false;
			});
		});
		</script>';

		$params=array(
		'otp_input_field_nm'=>'order_verify',
		);
		$otp_template_style =  smsalert_get_option( 'otp_template_style', 'smsalert_general', 'otp-popup-1.php');
		echo get_smsalert_template('template/'.$otp_template_style,$params);
	}

	public static function isFormEnabled()
	{
		return (is_plugin_active('woocommerce/woocommerce.php') && smsalert_get_option('buyer_checkout_otp', 'smsalert_general')=="on") ? true : false;
	}

	function routeData()
	{
		if(!array_key_exists('option', $_GET)) return;
		if(strcasecmp(trim($_GET['option']),'smsalert-woocommerce-checkout') == 0 || strcasecmp(trim($_GET['option']),'smsalert-woocommerce-post-checkout') == 0) $this->handle_woocommere_checkout_form($_POST);

		if(strcasecmp(trim($_GET['option']),'smsalert-woocommerce-block-checkout') == 0) $this->handle_woocommere_checkout_form($_POST);

		if(strcasecmp(trim($_GET['option']),'smsalert-woocommerce-validate-otp-form') == 0) $this->handle_otp_token_submitted($_POST);
	}
	function handle_woocommere_checkout_form($getdata)
	{
		SmsAlertUtility::checkSession();
		if(!empty($_GET['option']) && $_GET['option']=='smsalert-woocommerce-block-checkout')
		{
			SmsAlertUtility::initialize_transaction($this->formSessionVar2);
		}
		elseif(!empty($_GET['option']) && $_GET['option']=='smsalert-woocommerce-post-checkout')
		{
			SmsAlertUtility::initialize_transaction($this->formSessionVar3);
		}
		else
		{
			SmsAlertUtility::initialize_transaction($this->formSessionVar);
		}
		$phone_num 	= SmsAlertcURLOTP::checkPhoneNos($getdata['user_phone']);
		smsalert_site_challenge_otp('test',$getdata['user_email'],null, trim($phone_num),"phone");
	}

	function checkIfVerificationNotStarted()
	{
		if($this->popupEnabled) {return false;}
		
		SmsAlertUtility::checkSession();
		if(!isset($_SESSION[$this->formSessionVar])){
			wc_add_notice(__("Verify Code is a required field",'sms-alert'), 'error' );
			return TRUE;
		}
		return FALSE;
	}

	function checkIfVerificationCodeNotEntered()
	{
		//if(array_key_exists('order_verify', $_POST) && isset($_POST['order_verify'])) return FALSE;
		//wc_add_notice( SmsAlertMessages::showMessage('ENTER_PHONE_CODE'), 'error' );
		//return TRUE;
		
		if($this->popupEnabled) {return false;}
		
		SmsAlertUtility::checkSession();
		if(isset($_SESSION[$this->formSessionVar]) && empty($_POST['order_verify'])){
			wc_add_notice(__("Verify Code is a required field",'sms-alert'), 'error' );
			return TRUE;
		}
	}

	function add_custom_button()
	{
		if($this->guestCheckOutOnly && is_user_logged_in())  return;
		$this->show_validation_button_or_text(TRUE);
		$this->common_button_or_link_enable_disable_script();
		$otp_resend_timer 			= smsalert_get_option( 'otp_resend_timer', 'smsalert_general', '15');
		$action = (smsalert_get_option('post_order_verification', 'smsalert_general')=="on")? 'smsalert-woocommerce-post-checkout' : 'smsalert-woocommerce-checkout';
		
		$validate_before_sending_otp = smsalert_get_option('validate_before_send_otp', 'smsalert_general');
		
		echo ',counterRunning=false, $sa(".woocommerce-error").length>0&&$sa("html, body").animate({scrollTop:$sa("div.woocommerce").offset().top-50},1e3),';

		echo '$sa("#smsalert_otp_token_submit").click(function(o){ if(counterRunning){$sa("#myModal").show();return false;}';
			if($validate_before_sending_otp=='on')
			{
				echo '
				var error =0;
				$sa(".woocommerce-billing-fields .validate-required").not(".woocommerce-validated").find("input:not(:hidden),select").each(function( index ) {
				  if($sa(this).val()==""){error++;}
				  
				});
				
				$sa(".woocommerce-account-fields .validate-required").not(".woocommerce-validated").find("input:not(:hidden),select").each(function( index ) {
				  if($sa(this).val()==""){error++;}
				  
				});
				
				if($sa("#ship-to-different-address-checkbox").prop("checked")==true)
				{
					$sa(".woocommerce-shipping-fields .validate-required").not(".woocommerce-validated").find("input:not(:hidden),select").each(function( index ) {
					  if($sa(this).val()==""){error++;}
					});
				}
				if($sa(".woocommerce-checkout #terms").prop("checked")===false)
				{
					error=error + 1;
				}
				
				if(error>0){
					$sa(".woocommerce-checkout").submit();
					return false;
				}';
			}		
			echo 'var e=$sa("input[name=billing_email]").val(),';

			if(is_checkout() && smsalert_get_option('checkout_show_country_code', 'smsalert_general')=="on" && smsalert_get_option('post_order_verification', 'smsalert_general')!="on")
			{
				echo 'm=$sa(this).parents("form").find("input[name=billing_phone]").intlTelInput("getNumber"),';
			}
			else
			{
				echo 'm=$sa(this).parents("form").find("input[name=billing_phone]").val(),';
			}
			echo 'a=$sa("div.woocommerce");a.addClass("processing").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}),
			
				saInitOTPProcess(
					this,
					"'.site_url().'/?option='.$action.'",
					{user_email:e, user_phone:m},
					'.$otp_resend_timer.',
					function(resp){
						if(resp.result=="success"){$sa(".blockUI").hide()}else{$sa(".blockUI").hide()}
					},
					function(resp){

					}
				)
		}),';

		echo '$sa("form.woocommerce-checkout .smsalert_otp_validate_submit").click(function(o){
			counterRunning=false,clearInterval(interval),$sa(".smsalertModal").hide(),$sa(".sa-message").removeClass("woocommerce-message"),$sa(".smsalertModal .smsalert_validate_field").hide(),$sa(".woocommerce-checkout").submit()});});';

		echo ($this->otp_for_selected_gateways && $this->popupEnabled) ? '' : 'jQuery("input[name=woocommerce_checkout_place_order], button[name=woocommerce_checkout_place_order]").hide();';

		echo '</script>';
	}

	function add_custom_popup()
	{
		if($this->guestCheckOutOnly && is_user_logged_in())  return;
		$params=array(
			'otp_input_field_nm'=>'order_verify',
		);
		$otp_template_style =  smsalert_get_option( 'otp_template_style', 'smsalert_general', 'otp-popup-1.php');
		echo get_smsalert_template('template/'.$otp_template_style,$params);
	}

	function my_custom_checkout_field( $checkout )
	{
		if($this->guestCheckOutOnly && is_user_logged_in())  return;
		
		//echo '<div id="salert_message" style="display:none"></div>';
		$this->show_validation_button_or_text();
		woocommerce_form_field( 'order_verify', 
		array(
			'type'          => 'text',
			'class'         => array('form-row-wide'),
			'label'         => __("Verify Code ",'sms-alert'),
			'required'  	=> true,
			'placeholder'   => __("Enter Verification Code",'sms-alert'),
			), (is_object($checkout) ? $checkout->get_value( 'order_verify' ):""
		));
		
		$this->common_button_or_link_enable_disable_script();

		echo ',$sa(".woocommerce-error").length>0&&$sa("html, body").animate({scrollTop:$sa("div.woocommerce").offset().top-50},1e3),$sa("#smsalert_otp_token_submit").click(function(o){
		
		$sa(this).before("<div id=\"salert_message\" style=\"display:none\"></div>");
		var e=$sa("input[name=billing_email]").val(),';

		if(is_checkout() && smsalert_get_option('checkout_show_country_code', 'smsalert_general')=="on" && smsalert_get_option('post_order_verification', 'smsalert_general')!="on")
		{
		echo 'n=$sa(this).parents("form").find("input[name=billing_phone]").intlTelInput("getNumber"),';
		}
		else
		{
			echo 'n=$sa(this).parents("form").find("input[name=billing_phone]").val(),';
		}
		
		$post_order_verification = smsalert_get_option('post_order_verification', 'smsalert_general');
		$action = ($post_order_verification=="on") ? 'smsalert-woocommerce-post-checkout' : 'smsalert-woocommerce-checkout';


		echo 'a=$sa("div.woocommerce");a.addClass("processing").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}),$sa.ajax({url:"'.site_url().'/?option='.$action.'",type:"POST",data:{user_email:e, user_phone:n},crossDomain:!0,dataType:"json",success:function(o){ if(o.result=="success"){$sa(".blockUI").hide(),$sa("#salert_message").empty(),$sa("#salert_message").append(o.message),$sa("#salert_message").addClass("woocommerce-message"),$sa("#salert_message").show(),$sa("#order_verify").focus()}else{$sa(".blockUI").hide(),$sa("#salert_message").empty(),$sa("#salert_message").append(o.message),$sa("#salert_message").addClass("woocommerce-error"),$sa("#salert_message").show();} ;},error:function(o,e,n){}}),o.preventDefault()});});</script>';
	}

	function show_validation_button_or_text($popup=FALSE)
	{
		if(!$this->showButton) $this->showTextLinkOnPage();
		if($this->showButton) $this->showButtonOnPage();
	}

	function showTextLinkOnPage()
	{
		$otp_verify_btn_text = smsalert_get_option( 'otp_verify_btn_text', 'smsalert_general', '');
		echo '<div title="'.__("Please Enter a Phone Number to enable this link",'sms-alert').'"><a href="#" style="text-align:center;color:grey;pointer-events:none;" id="smsalert_otp_token_submit" class="" >'.$otp_verify_btn_text.'</a></div>';
	}

	function showButtonOnPage()
	{
		$otp_verify_btn_text = smsalert_get_option( 'otp_verify_btn_text', 'smsalert_general', '');
		echo '<button type="button" class="button alt sa-otp-btn-init" id="smsalert_otp_token_submit" disabled title="'
			.__("Please Enter a Phone Number to enable this link",'sms-alert').'" value="'
			.$otp_verify_btn_text.'" >'.$otp_verify_btn_text.'</button>';
	}

	function common_button_or_link_enable_disable_script()
	{
		echo '<script> jQuery(document).ready(function() {$sa = jQuery,';
		echo '$sa(".woocommerce-message").length>0&&($sa("#order_verify").focus(),$sa("#salert_message").addClass("woocommerce-message"));';
		if(!$this->showButton) $this->enabledDisableScriptForTextOnPage();
		if($this->showButton) $this->enableDisableScriptForButtonOnPage();
	}

	function enabledDisableScriptForTextOnPage()
	{
		echo '""!=$sa("input[name=billing_phone]").val()&&$sa("#smsalert_otp_token_submit").removeAttr("style"); $sa("input[name=billing_phone]").keyup(function(){
			$sa(this).val($sa(this).val().replace(/^0+/, "").replace(/\s+/g, ""));
			var phone = $sa(this).val();
			if(phone.replace(/\s+/g, "").match('.SmsAlertConstants::getPhonePattern().')) { $sa("#smsalert_otp_token_submit").removeAttr("style");} else{$sa("#smsalert_otp_token_submit").css({"color":"grey","pointer-events":"none"}); }
		})';
	}

	function enableDisableScriptForButtonOnPage()
	{
		echo '""!=$sa("input[name=billing_phone]").val()&&$sa("#smsalert_otp_token_submit").prop( "disabled", false );$sa("input[name=billing_phone]").keyup(function() {
			$sa(this).val($sa(this).val().replace(/^0+/, "").replace(/\s+/g, ""));
			var phone = $sa(this).val();
			if(phone.replace(/\s+/g, "").match('.SmsAlertConstants::getPhonePattern().')) {$sa("#smsalert_otp_token_submit").prop( "disabled", false );} else { $sa("#smsalert_otp_token_submit").prop( "disabled", true ); }})';
	}

	function my_custom_checkout_field_process()
	{
		if($this->guestCheckOutOnly && is_user_logged_in()) return;
		if(!$this->isPaymentVerificationNeeded()) return;
		if($this->checkIfVerificationNotStarted()) return;
		if($this->checkIfVerificationCodeNotEntered()) return;
		$this->handle_otp_token_submitted(FALSE);
	}

	function handle_otp_token_submitted($error)
	{
		//27-02-2021 validate before sending otp
		$validate_before_sending_otp = smsalert_get_option('validate_before_send_otp', 'smsalert_general');
		if($validate_before_sending_otp=='on' && $this->popupEnabled && empty($_POST['order_verify'])) {
			return;
		} 
		
		if(empty($_POST['billing_phone'])){return;}//added 09-02-2021
		$error 				= $this->processPhoneNumber();
		if(!$error) $this->processOTPEntered();
	}

	function isPaymentVerificationNeeded($payment_method=null)
	{
		if(!$this->otp_for_selected_gateways)
			return true;
		
		$payment_method 	= (!empty($_POST['payment_method']) ? $_POST['payment_method'] : $payment_method);
		return in_array($payment_method,$this->paymentMethods);
	}

	function processPhoneNumber()
	{
		SmsAlertUtility::checkSession();
		$phone_no = SmsAlertcURLOTP::checkPhoneNos($_POST['billing_phone']);
		if(array_key_exists('phone_number_mo', $_SESSION)&& strcasecmp($_SESSION['phone_number_mo'], $phone_no)!=0)
		{
			wc_add_notice(SmsAlertMessages::showMessage('PHONE_MISMATCH'), 'error' );
			return TRUE;
		}
	}

	function handle_failed_verification($user_login,$user_email,$phone_number)
	{
		SmsAlertUtility::checkSession();
		if(!isset($_SESSION[$this->formSessionVar]) && !isset($_SESSION[$this->formSessionVar2])  && !isset($_SESSION[$this->formSessionVar3])) return;
		if(isset($_SESSION[$this->formSessionVar2]))
		{
			wp_send_json(SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('INVALID_OTP'),'error'));
		}
		elseif(isset($_SESSION[$this->formSessionVar3]))
		{
				if(smsalert_get_option('checkout_otp_popup','smsalert_general')=="on"){
					wp_send_json( SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('INVALID_OTP'),'error'));
					exit();
				}
				else
				{
					wc_add_notice( SmsAlertUtility::_get_invalid_otp_method(), 'error' );
					wp_redirect($_SERVER['REQUEST_URI']);
					exit();
				}
		}
		else
		{
			wc_add_notice( SmsAlertUtility::_get_invalid_otp_method(), 'error' );
		}
	}

	function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
	{
		SmsAlertUtility::checkSession();
		if(!isset($_SESSION[$this->formSessionVar]) && !isset($_SESSION[$this->formSessionVar2]) && !isset($_SESSION[$this->formSessionVar3])) return;

		if(isset($_SESSION[$this->formSessionVar2]))
		{
			wp_send_json( SmsAlertUtility::_create_json_response("OTP Validated Successfully.",'success'));
			$this->unsetOTPSessionVariables();
			exit();
		}
		elseif(isset($_SESSION[$this->formSessionVar3]))//post order
		{
			$order_id 	= $_SESSION['sa_wc_order_key'];
			$output = update_post_meta($order_id,'_smsalert_post_order_verification', 1);
			
			unset($_SESSION['sa_wc_order_key']);
			
			if($output>0 && (smsalert_get_option('checkout_otp_popup', 'smsalert_general')=="on"))
			{
				wp_send_json( SmsAlertUtility::_create_json_response("OTP Validated Successfully.",'success'));
				$this->unsetOTPSessionVariables();
				exit();
			}
			else
			{
				$this->unsetOTPSessionVariables();
				wc_add_notice("OTP Validated Successfully.", 'success' );
				wp_redirect($_SERVER['REQUEST_URI']);
				exit();
			}
		}
		else
		{
			$this->unsetOTPSessionVariables();
		}
	}

	function enqueue_script_on_page()
	{
		if(is_checkout())
		{
			if($this->otp_for_selected_gateways==TRUE && smsalert_get_option('post_order_verification', 'smsalert_general')!='on')
			{
				wp_register_script( 'wccheckout', SA_MOV_URL . 'js/wccheckout.min.js' , array('jquery') ,SmsAlertConstants::SA_VERSION,true);

				wp_localize_script( 'wccheckout', 'otp_for_selected_gateways', array(
					'paymentMethods' => $this->paymentMethods,
					'ask_otp' => ($this->guestCheckOutOnly && is_user_logged_in() ? false : true),
				));

				wp_enqueue_script('wccheckout');
			}

			SmsAlertUtility::enqueue_script_for_intellinput();

			wp_register_script('smsalert-auth', SA_MOV_URL . 'js/otp-sms.min.js', array('jquery'), SmsAlertConstants::SA_VERSION, true );
			wp_enqueue_script('smsalert-auth');
		}
	}

	function processOTPEntered()
	{
		$this->validateOTPRequest();
	}

	function validateOTPRequest()
	{
		do_action('smsalert_validate_otp','order_verify');
	}

	public function unsetOTPSessionVariables()
	{
		unset($_SESSION[$this->txSessionId]);
		unset($_SESSION[$this->formSessionVar]);
		unset($_SESSION[$this->formSessionVar2]);
		unset($_SESSION[$this->formSessionVar3]);
	}

	public function is_ajax_form_in_play($isAjax)
	{
		SmsAlertUtility::checkSession();
		return (isset($_SESSION[$this->formSessionVar]) || isset($_SESSION[$this->formSessionVar2]) || isset($_SESSION[$this->formSessionVar3])) ? TRUE : $isAjax;
	}

	function handleFormOptions()
	{
		//add on 12/05/2020
		add_action( 'add_meta_boxes', array($this, 'add_send_sms_meta_box') );
		add_action( 'wp_ajax_wc_sms_alert_sms_send_order_sms', array( $this,'send_custom_sms'));
		add_action( 'woocommerce_new_customer_note', array($this, 'trigger_new_customer_note'), 10 );

		if(is_plugin_active('woocommerce/woocommerce.php')){
			add_action( 'sa_addTabs', array( $this, 'addTabs' ), 1 );
			add_filter('sAlertDefaultSettings',  __CLASS__ .'::addDefaultSetting',1);
		}
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this,'add_admin_general_order_variation_description'), 10, 1 );
	}	
	
	function add_admin_general_order_variation_description( $order ){
		$order_id = $order->get_id();
		$post_verification = get_post_meta( $order_id, '_smsalert_post_order_verification', true );
		if($post_verification)
		{
			echo  '
			<p><strong>SMS Alert Post Verified</strong></p>
			<span class="dashicons dashicons-yes" style="color: #fff;width: 22px;height: 22px;background: #07930b;border-radius: 25px;line-height: 22px;" title="SMS Alert Post Verified"></span>';
		}
	}

	public static function getOrderVariables(){

		$variables = array(
			'[order_id]' 					=> 'Order Id',
			'[order_status]' 				=> 'Order Status',
			'[order_amount]' 				=> 'Order Amount',
			'[order_date]' 					=> 'Order Date',
			'[store_name]' 					=> 'Store Name',
			'[item_name]' 					=> 'Product Name',
			'[item_name_qty]' 				=> 'Product Name with Quantity',
			'[billing_first_name]' 			=> 'Billing First Name',
			'[billing_last_name]' 			=> 'Billing Last Name',
			'[billing_company]' 			=> 'Billing Company',
			'[billing_address_1]' 			=> 'Billing Address 1',
			'[billing_address_2]' 			=> 'Billing Address 2',
			'[billing_city]' 				=> 'Billing City',
			'[billing_state]' 				=> 'Billing State',
			'[billing_postcode]' 			=> 'Billing Postcode',
			'[billing_country]' 			=> 'Billing Country',
			'[billing_email]' 				=> 'Billing Email',
			'[billing_phone]' 				=> 'Billing Phone',

			'[shipping_first_name]'			=> 'Shipping First Name',
			'[shipping_last_name]' 			=> 'Shipping Last Name',
			'[shipping_company]' 			=> 'Shipping Company',
			'[shipping_address_1]' 			=> 'Shipping Address 1',
			'[shipping_address_2]' 			=> 'Shipping Address 2',
			'[shipping_city]' 				=> 'Shipping City',
			'[shipping_state]' 				=> 'Shipping State',
			'[shipping_postcode]' 			=> 'Shipping Postcode',
			'[shipping_country]' 			=> 'Shipping Country',

			'[order_currency]' 				=> 'Order Currency',
			'[payment_method]' 				=> 'Payment Method',
			'[payment_method_title]' 		=> 'Payment Method Title',
			'[shipping_method]' 			=> 'Shipping Method',
			'[shop_url]' 					=> 'Shop Url',
		);
		return $variables;
	}

	public static function getCustomerTemplates()
	{
		$order_statuses 						= is_plugin_active('woocommerce/woocommerce.php') ? wc_get_order_statuses() : array();

		$smsalert_notification_status 			= smsalert_get_option( 'order_status', 'smsalert_general', '');
		$smsalert_notification_onhold 			= (is_array($smsalert_notification_status) && array_key_exists('on-hold', $smsalert_notification_status)) ? $smsalert_notification_status['on-hold'] : 'on-hold';
		$smsalert_notification_processing 		= (is_array($smsalert_notification_status) && array_key_exists('processing', $smsalert_notification_status)) ? $smsalert_notification_status['processing'] : 'processing';
		$smsalert_notification_completed 		= (is_array($smsalert_notification_status) && array_key_exists('completed', $smsalert_notification_status)) ? $smsalert_notification_status['completed'] : 'completed';
		$smsalert_notification_cancelled 		= (is_array($smsalert_notification_status) && array_key_exists('cancelled', $smsalert_notification_status)) ? $smsalert_notification_status['cancelled'] : 'cancelled';

		$smsalert_notification_notes 			= smsalert_get_option( 'buyer_notification_notes', 'smsalert_general', 'on');
		$sms_body_new_note 						= smsalert_get_option( 'sms_body_new_note', 'smsalert_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_NOTE') );

		$templates = array();
		foreach($order_statuses as $ks  => $order_status){

			$prefix = 'wc-';
			$vs = $ks;
			if (substr($vs, 0, strlen($prefix)) == $prefix){
				$vs = substr($vs, strlen($prefix));
			}

			$current_val 						= (is_array($smsalert_notification_status) && array_key_exists($vs, $smsalert_notification_status)) ? $smsalert_notification_status[$vs] : $vs;

			$current_val 						= ($current_val==$vs)?"on":'off';

			$checkboxNameId						= 'smsalert_general[order_status]['.$vs.']';
			$textareaNameId						= 'smsalert_message[sms_body_'.$vs.']';

			$default_template 					= SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_'.str_replace('-', '_', strtoupper($vs)));
			$text_body 							= smsalert_get_option('sms_body_'.$vs, 'smsalert_message', (($default_template!='') ? $default_template : SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_STATUS_CHANGED')));

			$templates[$ks]['title'] 			= 'When Order is '.ucwords($order_status);
			$templates[$ks]['enabled'] 			= $current_val;
			$templates[$ks]['status'] 			= $vs;
			$templates[$ks]['chkbox_val'] 		= $vs;
			$templates[$ks]['text-body'] 		= $text_body;
			$templates[$ks]['checkboxNameId'] 	= $checkboxNameId;
			$templates[$ks]['textareaNameId'] 	= $textareaNameId;
			$templates[$ks]['moreoption'] 		= 1;
			$templates[$ks]['token'] 			= self::getvariables($vs);
		}

		//new note
		$new_note 								= self::getOrderVariables();
		$new_note["note"] 						= "Order Note";
		$templates['new-note']['title'] 		= 'When a new note is added to order';
		$templates['new-note']['enabled'] 		= $smsalert_notification_notes;
		$templates['new-note']['status'] 		= 'new-note';
		$templates['new-note']['text-body'] 	= $sms_body_new_note;
		$templates['new-note']['checkboxNameId']= 'smsalert_general[buyer_notification_notes]';
		$templates['new-note']['textareaNameId']= 'smsalert_message[sms_body_new_note]';
		$templates['new-note']['token'] 		= $new_note;
		return $templates;
	}

	public static function getAdminTemplates()
	{
		$order_statuses							= is_plugin_active('woocommerce/woocommerce.php') ? wc_get_order_statuses() : array();

		$templates = array();
		foreach($order_statuses as $ks  => $order_status){

			$prefix = 'wc-';
			$vs 	= $ks;
			if (substr($vs, 0, strlen($prefix)) == $prefix){
				$vs = substr($vs, strlen($prefix));
			}

			$current_val 						= smsalert_get_option( 'admin_notification_'.$vs, 'smsalert_general', 'on');
			$checkboxNameId						= 'smsalert_general[admin_notification_'.$vs.']';
			$textareaNameId						= 'smsalert_message[admin_sms_body_'.$vs.']';
			$default_template 					= SmsAlertMessages::showMessage('DEFAULT_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs)));
			$text_body 							= smsalert_get_option('admin_sms_body_'.$vs, 'smsalert_message', (($default_template!='') ? $default_template : SmsAlertMessages::showMessage('DEFAULT_ADMIN_SMS_STATUS_CHANGED')));

			$templates[$ks]['title'] 			= 'When Order is '.ucwords($order_status);
			$templates[$ks]['enabled'] 			= $current_val;
			$templates[$ks]['status'] 			= $vs;
			$templates[$ks]['text-body'] 		= $text_body;
			$templates[$ks]['checkboxNameId'] 	= $checkboxNameId;
			$templates[$ks]['textareaNameId'] 	= $textareaNameId;
			$templates[$ks]['moreoption'] 		= 1;
			$templates[$ks]['token'] 			= self::getvariables($vs);
		}
		return $templates;
	}

	/*add tabs to smsalert settings at backend*/
	public static function addTabs($tabs=array())
	{
		$customer_param=array(
			'checkTemplateFor'											=> 'wc_customer',
			'templates'													=> self::getCustomerTemplates(),
		);

		$admin_param=array(
			'checkTemplateFor'											=>'wc_admin',
			'templates'													=>self::getAdminTemplates(),
		);
		$tabs['woocommerce']['nav']										= 'Woocommerce';
		$tabs['woocommerce']['icon']									= 'dashicons-list-view';
		$tabs['woocommerce']['inner_nav']['wc_customer']['title']		= 'Customer Notifications';
		$tabs['woocommerce']['inner_nav']['wc_customer']['tab_section'] = 'customertemplates';
		$tabs['woocommerce']['inner_nav']['wc_customer']['tabContent']	= self::getContentFromTemplate('views/message-template.php',$customer_param);
		$tabs['woocommerce']['inner_nav']['wc_customer']['first_active']= true;
		$tabs['woocommerce']['inner_nav']['wc_admin']['title']			= 'Admin Notifications';
		$tabs['woocommerce']['inner_nav']['wc_admin']['tab_section'] 	= 'admintemplates';
		$tabs['woocommerce']['inner_nav']['wc_admin']['tabContent']		= self::getContentFromTemplate('views/message-template.php',$admin_param);
		return $tabs;
	}
	
	public static function getContentFromTemplate($path,$params=array())
	{
		return get_smsalert_template($path,$params);
	}

	/*add default settings to savesetting in setting-options*/
	public static function addDefaultSetting($defaults=array())
	{
		$order_statuses 												= is_plugin_active('woocommerce/woocommerce.php') ? wc_get_order_statuses() : array();
		foreach($order_statuses as $ks => $vs)
		{
			$prefix = 'wc-';
			if (substr($ks, 0, strlen($prefix)) == $prefix) {
				$ks = substr($ks, strlen($prefix));
			}
			$defaults['smsalert_general']['admin_notification_'.$ks]	= 'off';
			$defaults['smsalert_general']['order_status'][$ks] 			= '';
			$defaults['smsalert_message']['admin_sms_body_'.$ks]		= '';
			$defaults['smsalert_message']['sms_body_'.$ks]				= '';
		}
		return $defaults;
	}

	//commented on 24/01/2021
	//public static function pharse_sms_body( $content, $order_status, $order, $order_note, $rma_id = '' ) 
	public static function pharse_sms_body($sms_data,$order_id) 
	{
		if(empty($sms_data['sms_body'])) return $sms_data;
		
		$content 							= $sms_data['sms_body'];
		$order_variables					= get_post_custom($order_id);
		$order 								= new WC_Order($order_id); //new added
		$order_status						= $order->get_status();
		$order_items 						= $order->get_items();
		$order_note							= (!empty($sms_data['note'])?$sms_data['note']:'');
		$rma_id								= (!empty($sms_data['rma_id'])?$sms_data['rma_id']:'');

		if(strpos($content,'orderitem')!==false)
		{
			$content 						= self::sa_parse_orderItem_data($order_items,$content);
		}

		$item_name							= implode(", ",array_map(function($o){return $o['name'];},$order_items));
		$item_name_with_qty					= implode(", ",array_map(function($o){return sprintf("%s [%u]", $o['name'], $o['qty']);},$order_items));
		$store_name 						= get_bloginfo();
		$shop_url							= get_site_url();
		$date_format 	= 'F j, Y';
		$date_tag 		= '[order_date]';

		if(preg_match_all('/\[order_date.*?\]/',$content,$matched))
		{
			$date_tag = $matched[0][0];
			$date_params = SmsAlertUtility::parseAttributesFromTag($date_tag);
			$date_format = array_key_exists('format', $date_params) ? $date_params['format'] : "F j, Y";
		}

		$find = array(
            '[order_id]',
			$date_tag,
            '[order_status]',
            '[rma_status]',
            '[first_name]',
            '[item_name]',
            '[item_name_qty]',
            '[order_amount]',
            '[note]',
            '[rma_number]',
            '[store_name]',
			'[shop_url]',
			'[order_pay_url]',
			'[wc_order_id]',
        );
        $replace = array(
            $order->get_order_number(),
            $order->get_date_created()->date($date_format),
            $order_status,
            $order_status,
            '[billing_first_name]',
            wp_specialchars_decode($item_name),
			wp_specialchars_decode($item_name_with_qty),
			$order->get_total(),
			$order_note,
			$rma_id,
			$store_name,
			$shop_url,
			esc_url($order->get_checkout_payment_url()),
			$order_id
        );

        $content = str_replace( $find, $replace, $content );
		foreach ($order_variables as &$value) {
			$value = $value[0];
		}
		unset($value);

		$order_variables = array_combine(
			array_map(function($key){ return '['.ltrim($key, '_').']'; }, array_keys($order_variables)),
			$order_variables
		);
        $content = str_replace( array_keys($order_variables), array_values($order_variables), $content );
		
		
		$sms_data['sms_body'] = $content;
		
		//return $content;
		return $sms_data;
    }
	
	public	function send_custom_sms($data)
	{
		$buyer_sms_data 				= array();
		$buyer_sms_data['number']   	= get_post_meta( $_POST['order_id'], '_billing_phone', true );
		$buyer_sms_data['sms_body'] 	= $_POST['sms_body'];
		//$buyer_sms_data				= self::pharse_sms_body($buyer_sms_data,$_POST['order_id']);
		$buyer_sms_data 				= apply_filters('sa_wc_order_sms_customer_before_send', $buyer_sms_data, $_POST['order_id']);
		echo SmsAlertcURLOTP::sendsms( $buyer_sms_data );
		exit();
	}
	
	function trigger_new_customer_note( $data ) {

		if(smsalert_get_option('buyer_notification_notes', 'smsalert_general')=="on")
		{
			$order_id					= $data['order_id'];
			$order						= new WC_Order( $order_id );
			$buyer_sms_body         	= smsalert_get_option( 'sms_body_new_note', 'smsalert_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_NOTE') );
			$buyer_sms_data 			= array();
			$buyer_sms_data['number']   = get_post_meta( $data['order_id'], '_billing_phone', true );
			$buyer_sms_data['sms_body'] = $buyer_sms_body;
			$buyer_sms_data['note'] 	= $data['customer_note'];
			
			$buyer_sms_data 			= apply_filters('sa_wc_order_sms_customer_before_send', $buyer_sms_data, $order_id);
			
			$buyer_response 			= SmsAlertcURLOTP::sendsms( $buyer_sms_data );
			$response					= json_decode($buyer_response,true);

			if( $response['status']	== 'success' ) {
				$order->add_order_note( __( 'Order note SMS Sent to buyer', 'smsalert' ) );
			} else {
				$order->add_order_note( __($response['description']['desc'], 'smsalert' ) );
			}
		}
	}
	//add on 12/05/2020
	function add_send_sms_meta_box(){
		add_meta_box(
			'wc_sms_alert_send_sms_meta_box',
			'SMS Alert (Custom SMS)',
			array($this, 'display_send_sms_meta_box'),
			'shop_order',
			'side',
			'default'
		);
	}
	//add on 12/05/2020
	function display_send_sms_meta_box($data){
		global $woocommerce, $post;
		$order 						 = new WC_Order($post->ID);
		$order_id 					 = $post->ID;

		$username 					 = smsalert_get_option( 'smsalert_name', 'smsalert_gateway' );
		$password 					 = smsalert_get_option( 'smsalert_password', 'smsalert_gateway' );
		$result 					 = SmsAlertcURLOTP::get_templates($username, $password);
		$templates 					 = json_decode($result, true);
		?>
		<select name="smsalert_templates" id="smsalert_templates" style="width:87%;" onchange="return selecttemplate(this, '#wc_sms_alert_sms_order_message');">
		<option value=""><?php  _e( 'Select Template', 'sms-alert' ) ?></option>
		<?php
		if(array_key_exists('description', $templates) && (!array_key_exists('desc', $templates['description']))) {
		foreach($templates['description'] as $template) {
		?>
		<option value="<?php echo $template['Smstemplate']['template'] ?>"><?php echo $template['Smstemplate']['title'] ?></option>
		<?php } } ?>
		</select>
		<span class="woocommerce-help-tip" data-tip="You can add templates from your www.smsalert.co.in Dashboard"></span>
		<p><textarea type="text" name="wc_sms_alert_sms_order_message" id="wc_sms_alert_sms_order_message" class="input-text" style="width: 100%;" rows="4" value=""></textarea></p>
		<input type="hidden" class="wc_sms_alert_order_id" id="wc_sms_alert_order_id" value="<?php echo $order_id;?>" >
		<p><a class="button tips" id="wc_sms_alert_sms_order_send_message" data-tip="<?php __( 'Send an SMS to the billing phone number for this order.', 'sms-alert' ) ?>"><?php _e( 'Send SMS', 'sms-alert' ) ?></a>
		<span id="wc_sms_alert_sms_order_message_char_count" style="color: green; float: right; font-size: 16px;">0</span></p>
		<?php
	}


	public static function sa_wc_get_order_item_meta($item,$code)
	{
		$item_data   					= $item->get_data();

		foreach ($item_data as $i_key => $i_val )
		{
			if($i_key==$code)
			{
				$val 					= $i_val;
				break;
			}
			else
			{
				if($i_key=='meta_data')
				{
					$item_meta_data 	= $item->get_meta_data();
					foreach ($item_meta_data as $mkey => $meta )
					{
						if($code==$mkey)
						{
							$meta_value = $meta->get_data();
							$temp 		= maybe_unserialize($meta_value['value']);
							if(is_array($temp))
							{
								$val 	= $temp;
								break;
							}
							else
							{
								$val 	= $meta_value['value'];
								break;
							}
						}
					}
				}
			}
		}
		return $val;
	}

	public static function recursive_change_key($arr, $set ='') {
        if(is_numeric($set)) {
			$set 	 			 = '';
		}
		if($set != '') {
				$set 			 = $set.'.';
		}
		if (is_array($arr)) { 
    		$newArr  = array();
    		foreach ($arr as $k => $v) {
    		    $newArr[$set.$k] = is_array($v) ? self::recursive_change_key($v, $set.$k) : $v;
    		}
    		return $newArr;
    	}
    	return $arr;
    }

	/*
		sa_parse_orderItem_data
		attributes can be used : order_id,name,product_id,variation_id,quantity,tax_class,subtotal,subtotal_tax,total,total_tax
		properties : list="2" , format="%s,$d"
		[orderitem list='2' name product_id quantity subtotal]
	*/
	public static function sa_parse_orderItem_data($orderItems,$content)
	{
		$pattern 						= get_shortcode_regex();
		preg_match_all('/\[orderitem(.*?)\]/', $content, $matches );
		$shortcode_tags 				= $matches[0];
		$parsed_codes					= array();
		foreach($shortcode_tags as $tag)
		{
			$r_tag 						= preg_replace( "/\[|\]+/", '', $tag );
			$parsed_codes[$tag] 		= shortcode_parse_atts($r_tag);
		}
		$r_text 						= '';
		$replaced_arr					= array();
		foreach($parsed_codes as $token => &$parsed_code)
		{
			$replace_text 				= '';
			$item_iterate 				= (!empty($parsed_code['list']) && $parsed_code['list']>0) ? $parsed_code['list'] : 0;
			$format		  				= (!empty($parsed_code['format'])) ? $parsed_code['format'] : '';

			$prop=array();
			foreach($parsed_code as $kcode => $code)
			{
				$tmp = array();
				if(!in_array($kcode,array('list','format')))
				{
					$parts 				= array();
					if(strpos($code,".")!==FALSE)
					{
						$parts 			= explode(".",$code);
						$code  			= array_shift($parts);
					}
					foreach ( $orderItems as $item_id => $item )
					{
						$attr_val 	    = (!empty($item[$code])) ? $item[$code] : self::sa_wc_get_order_item_meta($item,$code);

						if(!empty($parts))
						{
							$attr_val   = self::getRecursiveVal($parts,$attr_val);
							$attr_val   = is_array($attr_val) ? 'Array' : $attr_val;
						}

						if(!empty($format)){
							$prop[]	  	=  $attr_val;
						}
						else
						{
							$tmp[]    	= $attr_val;
						}
					}
				}
			}

			if(!empty($format))
			{
				$tmp[] 				 	= vsprintf($format,$prop);
			}
			$replaced_arr[$token]    	= implode(", ",$tmp);
		}
		return str_replace(array_keys($replaced_arr),array_values($replaced_arr),$content);
	}

	public static function getRecursiveVal($array , $attr)
	{
		foreach($array as $part)
		{
			if(is_array($part))
			{
				$attr = self::getRecursiveVal($part , $attr);
			}
			else
			{
				$attr = (!empty($attr[$part])) ? $attr[$part] : '';
			}
		}
		return $attr;
	}

	//add on 25/05/2020
	public static function trigger_after_order_place( $order_id, $old_status, $new_status ) {

		if( !$order_id ) {
            return;
        }

		$order 							= new WC_Order( $order_id );
        $admin_sms_data 				= $buyer_sms_data = array();

        $order_status_settings  		= smsalert_get_option( 'order_status', 'smsalert_general', array() );
        $admin_phone_number     		= smsalert_get_option( 'sms_admin_phone', 'smsalert_message', '' );
		$admin_phone_number 			= str_replace('postauthor','post_author',$admin_phone_number);

        if( count( $order_status_settings ) < 0 ) {
            return;
        }
        if( in_array( $new_status, $order_status_settings ) && $order->get_parent_id() == 0 )
		{
			$default_buyer_sms 			= defined('SmsAlertMessages::DEFAULT_BUYER_SMS_'.str_replace(" ","_",strtoupper($new_status)))   ? constant('SmsAlertMessages::DEFAULT_BUYER_SMS_'.str_replace(" ","_",strtoupper($new_status)))   : SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_STATUS_CHANGED');

			$buyer_sms_body 			= smsalert_get_option( 'sms_body_'.$new_status, 'smsalert_message', $default_buyer_sms);
			$buyer_sms_data['number'] 	= get_post_meta( $order_id, '_billing_phone', true );
			$buyer_sms_data['sms_body'] = $buyer_sms_body;
			
			
			$buyer_sms_data 			= apply_filters('sa_wc_order_sms_customer_before_send', $buyer_sms_data, $order_id);
			$buyer_response 			= SmsAlertcURLOTP::sendsms( $buyer_sms_data );
			$response					= json_decode($buyer_response, true);

			if( $response['status']=='success' ) {
				$order->add_order_note( __('SMS Send to buyer Successfully.', 'smsalert' ) );
			} else {
				if(isset($response['description']) && is_array($response['description']) && array_key_exists('desc', $response['description']))
				{
					$order->add_order_note( __($response['description']['desc'], 'smsalert' ) );
				}
				else
				{
					$order->add_order_note( __($response['description'], 'smsalert' ) );
				}
			}
		}

		if(smsalert_get_option( 'admin_notification_'.$new_status, 'smsalert_general', 'on' ) == 'on' && $admin_phone_number!='')
		{
			//send sms to post author
			$has_sub_order 				= metadata_exists('post',$order_id,'has_sub_order');
			if(
				(strpos($admin_phone_number,'post_author') !== false) &&
				($order->get_parent_id() != 0 || ($order->get_parent_id() == 0 && $has_sub_order == '')))
			{
				$order_items 			= $order->get_items();
				$first_item 			= current($order_items);
				$prod_id 				= $first_item['product_id'];
				$product 				= wc_get_product( $prod_id );
				$author_no 				= get_the_author_meta('billing_phone', get_post($prod_id)->post_author);

				if($order->get_parent_id() == 0) {
					$admin_phone_number = str_replace('post_author', $author_no, $admin_phone_number);
				}
				else {
					$admin_phone_number = $author_no;
				}
			}

			$default_template 			= SmsAlertMessages::showMessage('DEFAULT_ADMIN_SMS_'.str_replace('-', '_', strtoupper($new_status)));

			$default_admin_sms 			= (($default_template!='') ? $default_template : SmsAlertMessages::showMessage('DEFAULT_ADMIN_SMS_STATUS_CHANGED'));

			$admin_sms_body  			= smsalert_get_option( 'admin_sms_body_'.$new_status, 'smsalert_message', $default_admin_sms );
			$admin_sms_data['number']   = $admin_phone_number;
			$admin_sms_data['sms_body'] = $admin_sms_body;

			$admin_sms_data = apply_filters('sa_wc_order_sms_admin_before_send', $admin_sms_data, $order_id);

			$admin_response             = SmsAlertcURLOTP::sendsms( $admin_sms_data );
			$response=json_decode($admin_response,true);
			if( $response['status']=='success' ) {
				$order->add_order_note( __( 'SMS Sent Successfully.', 'smsalert' ) );
			} else {
				if(is_array($response['description']) && array_key_exists('desc', $response['description']))
				{
					$order->add_order_note( __($response['description']['desc'], 'smsalert' ) );
				}
				else {
					$order->add_order_note( __($response['description'], 'smsalert' ) );
				}
			}
		}
    }

	public static function getvariables($status=NULL)
	{
		$variables = self::getOrderVariables();
		if(in_array($status,array("pending","failed")))
		{
			$variables = array_merge($variables,  array(
				'[order_pay_url]' => 'Order Pay URL',
			));
		}
		
		$variables = apply_filters('sa_wc_variables',$variables,$status);//added on 05-05-2020
		return $variables;
	}
	/*handle after post verification */
	function send_post_order_otp( $title,$order) 
	{
		SmsAlertUtility::checkSession();
		$order_id = $order->get_id();
		$_SESSION['sa_wc_order_key'] = $order_id;
		$post_order_verification = smsalert_get_option( 'post_order_verification', 'smsalert_general');
		
		
		$verified = false;
		if($post_order_verification!='on') return;
		if($this->guestCheckOutOnly && is_user_logged_in())  return;	
		if ( ! $order_id )
			return;	
		
		// Get an instance of the WC_Order object
		//$order = wc_get_order( $order_id );//temporary
		if(!$this->isPaymentVerificationNeeded($order->get_payment_method())) return;

		// Allow code execution only once 
		if(!get_post_meta( $order_id, '_smsalert_post_order_verification', true ) ) {	
			$billing_phone = $order->get_billing_phone();
			$otp_verify_btn_text = smsalert_get_option( 'otp_verify_btn_text', 'smsalert_general', '');
			
			echo "<div class='post_verification_section'><p>Your order has been placed but your mobile number is not verified yet. Please verify your mobile number.</p>";
			
			if($this->popupEnabled)
			{
				echo "<form class='woocommerce-form woocommerce-post-checkout-form' method='post'>";
				echo "<p class='woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide'>";
				echo "<input type='hidden' name='billing_phone' class='sa-phone-field' value=".$billing_phone.">";
				echo "<input type='hidden' name='billing_email' >";
				echo "</p>";
				echo $this->add_custom_button();
				echo $this->add_custom_popup();
				echo "</form>";
				
				echo "<script>";
				echo 'jQuery(document).on("click", ".woocommerce-post-checkout-form .smsalert_otp_validate_submit",function(){
						var current_form = jQuery(this).parents("form");
						var action_url = "'.site_url().'/?option=smsalert-validate-otp-form";
						var data = current_form.serialize()+"&otp_type=phone&from_both=";
						sa_validateOTP(this,action_url,data,function(){
							current_form.submit()
						});
						return false;
					});
					jQuery(".woocommerce-thankyou-order-received").hide();';
				echo "</script>";
				
				
			}
			else
			{
				echo "<form class='woocommerce-form' method='post'>";
				echo "<p class='woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide'>";
				echo "<input type='hidden' name='billing_email'>";
				echo "<input type='hidden' name='billing_phone' class='billing_phone' value=".$billing_phone.">";
				echo "</p>";
				$this->my_custom_checkout_field($checkout=array());
				echo "<p class='woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide'>";
				echo "<input type='submit' class='button sa-hide' value='Submit'>";
				echo "</p>";
				echo "</form>";
			}
			echo "</div>";
			echo "<style>.post_verification_section{padding: 1em 1.618em;border: 1px solid #f2f2f2;background: #fff;box-shadow: 10px 5px 5px -6px #ccc;}</style>";
			if(!empty($_REQUEST['order_verify']))
			{
				if(!$this->popupEnabled)
				{
					if($this->checkIfVerificationCodeNotEntered()) return;
					$this->handle_otp_token_submitted(FALSE);
				}
			}
		}
		else
		{
			$this->unsetOTPSessionVariables();
			return __("Thank you, Your mobile number has been verified successfully.","sms-alert");
		}
	}
}
new WooCommerceCheckOutForm;
?>
<?php
class sa_all_order_variable
{
	public function __construct() {
		add_action( 'woocommerce_after_register_post_type', array($this, 'routeData'), 10, 1 );
	}

	public function routeData()
	{
		if(!empty($_REQUEST['option']) && $_REQUEST['option']=='fetch-order-variable' && !empty($_REQUEST['order_id']))
		{
			$order_id = $_REQUEST['order_id'];
			$tokens	  = array();

			global $woocommerce, $post;

			$order 	  = new WC_Order($order_id);

			//Order Detail
			$order_variables	= get_post_custom($order_id);

			$variables=array();
			foreach ($order_variables as $meta_key => &$value) {
				$temp = maybe_unserialize($value[0]);

				if(is_array($temp))
				{
					$variables[$meta_key] = $temp;
				}
				else
				{
					$variables[$meta_key] = $value[0];
				}
			}
			$variables['order_status'] 	= $order->get_status();
			$variables['order_date'] 	= $order->get_date_created();;

			$tokens['Order details'] 	= $variables;
			//OrderItem & OrderItemMeta
			$item_variables = array();
			foreach ($order->get_items() as $item_key => $item ){
				$item_data  = $item->get_data();
				$tmp1 = array();
				foreach ($item_data as $i_key => $i_val ){
					if($i_key=='meta_data'){
						$item_meta_data = $item->get_meta_data();
						foreach ($item_meta_data as $mkey => $meta ){

							$meta_value = $meta->get_data();
							$temp 		= maybe_unserialize($meta_value['value']);

							if(is_array($temp))
							{
								$tmp1["orderitem ".$meta_value['key']] = $temp;
							}
							else
							{
								$tmp1["orderitem ".$meta_value['key']] = $meta_value['value'];
							}
						}
					}
					else
					{
						$tmp1["orderitem ".$i_key] = $i_val;
					}
				}
				$item_variables[] = $tmp1;
			}
			$item_variables = WooCommerceCheckOutForm::recursive_change_key($item_variables);

			$tokens['Order details']['Order Items'] = $item_variables;
			wp_send_json($tokens);
			exit();
		}
	}
}
new sa_all_order_variable;
?>
<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class All_Order_List extends WP_List_Table {

	function __construct()
	{
		parent::__construct(array(
			'singular' => 'allordervaribale',
			'plural' => 'allordervariables',
		));
	}

	/*get all subscriber info*/
	public static function get_all_order() {
		global $wpdb;

		$sql 	= "SELECT * FROM {$wpdb->prefix}posts  WHERE post_type = 'shop_order' && post_status != 'auto-draft' ORDER BY post_date desc LIMIT 5";

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	public function no_items() {
	  _e( 'No Order.', 'smsalert' );
	}

	function column_default($item, $column_name)
	{
		return $item[$column_name];
	}

	function column_cb($item)
	{
		return sprintf(
			'<input type="checkbox" name="ID[]" value="%s" />',
			$item['ID']
		);
	}

	function column_post_status($item)
	{
		$post_status = sprintf('<button class="button-primary"/>%s</a>',str_replace("wc-","",$item['post_status']));
		return $post_status;
	}

	function column_post_date($item)
	{
		$date 	= date("d-m-Y", strtotime($item['post_date']));;
		return $date;
	}

	function get_columns() {
	  $columns = [
		'ID' => __( 'Order'),
		'post_date' => __( 'Date'),
		'post_status'    => __( 'Status'),
	  ];

	  return $columns;
	}

	public function prepare_items() {

		$columns 		= $this->get_columns();
		$this->items 	= self::get_all_order();

		// here we configure table headers, defined in our methods
		$this->_column_headers = array($columns);

		return $this->items;
	}
}

function all_order_variable_admin_menu()
{
	add_submenu_page( null, 'All Order Variable','All Order Variable', 'manage_options', 'all-order-variable', 'all_order_variable_page_handler');
}

add_action('admin_menu', 'all_order_variable_admin_menu');

function all_order_variable_page_handler()
{
	global $wpdb;

    $table_data = new All_Order_List();
	$data 		= $table_data->prepare_items();
?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2 class="title">Order List</h2>
	<form id="order-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table_data->display() ?>
    </form>
	<div id="sa_order_variable" class="sa_variables" style="display:none">
		<h3 class="h3-background">Select your variable <span id="order_id" class="alignright"><?php echo $order_id; ?></span></h3>
		<ul id="order_list"></ul>
	</div>
</div>
<script>
jQuery(document).ready(function(){
    jQuery("tbody tr").addClass("order_click");

	jQuery(".order_click").click(function(){
		var id = jQuery(this).find(".ID").text().replace(/\D/g,'');
		jQuery("#order-table, .title").hide();
		jQuery("#sa_order_variable").show();
		jQuery("#order_id").html('Order Id: '+id);

		if(id != ''){
			jQuery.ajax({
				url         : "<?php echo admin_url();?>?option=fetch-order-variable",
				data        : {order_id:id},
				dataType	: 'json',
				success: function(data)
				{
					var arr1	= data;
					var content1 = parseVariables(arr1);

					jQuery('ul#order_list').html(content1);

					jQuery("ul").prev("a").addClass("nested");

					jQuery('ul#order_list, ul#order_item_list').css('textTransform', 'capitalize');

					jQuery(".nested").parent("li").css({"list-style":"none"});

					jQuery("ul#order_list li ul:first").show();
					jQuery("ul#order_list").show();
					jQuery("ul#order_list li a:first").addClass('nested-close');

					toggleSubMenu();
					addToken();
				},
				error:function (e,o){
				}
			});
		}

	});

	function parseVariables(data,prefix='')
	{
		text = '';
		jQuery.each(data,function(i,item){


			if(typeof item === 'object')
			{
				var nested_key = i.toString().replace(/_/g," ").replace(/orderitem/g,"");
				var key = i.toString().replace(/^_/i,"");



				if(nested_key != ''){
					text+='<li><a href="#" value="['+key+']">'+nested_key+'</a><ul style="display:none">';
					text+= parseVariables(item,prefix);
					text+="</li></ul>";
				}
			}
			else
			{

				var j 		= i.toString();
				var key 	= i.toString().replace(/_/g," ").replace(/orderitem/g,"");
				var title 	= item;
				var val 	= j.toString().replace(/^_/i,"");


				text+='<li><a href="#" value="['+val+']" title="'+title+'">'+key+'</a></li>';
			}
	   });
	   return text;
	}

	function toggleSubMenu(){
		jQuery("a.nested").click(function(){
			jQuery(this).parent('li').find('ul:first').toggle();
			if(jQuery(this).hasClass("nested-close")){
				jQuery(this).removeClass("nested-close");
			}else{
				jQuery(this).addClass("nested-close");
			}
			return false;
		});
	}

	function addToken(){
		jQuery('.sa_variables a').click( function() {
			if(jQuery(this).hasClass("nested")){
				return false;
			}
			var token = jQuery(this).attr('value');
			window.parent.postMessage(token, '*');
		});
	}
	return false;
});
</script>
<?php } ?>