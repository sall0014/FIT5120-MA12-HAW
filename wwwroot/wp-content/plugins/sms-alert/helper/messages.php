<?php
if (! defined( 'ABSPATH' )) exit;
class SmsAlertMessages
{
	function __construct()
	{
		//created an array instead of messages instead of constant variables for Translation reasons.
		define("SALRT_MESSAGES", serialize( array(
			//General Messages
			"OTP_RANGE" 									=> __("Only digits within range 4-8 are allowed.",'sms-alert'),
			"SEND_OTP"  									=> __("Send OTP",'sms-alert'),
			"RESEND_OTP"  									=> __("Resend OTP",'sms-alert'),
			"VALIDATE_OTP"  								=> __("Validate OTP",'sms-alert'),
			"RESEND"  										=> __("Resend",'sms-alert'),
			"Phone"  										=> __("Phone",'sms-alert'),
			"INVALID_OTP"  									=> __("Invalid one time passcode. Please enter a valid passcode.",'sms-alert'),
			"ENTER_PHONE_CODE"  							=> __("Please enter the verification code sent to your phone.",'sms-alert'),			
			"CHANGE_PWD"  									=> __("Please change Your password",'sms-alert'),			
			"ENTER_PWD"  									=> __("Please enter your password.",'sms-alert'),			
			"PWD_MISMATCH"  								=> __("Passwords do not match.",'sms-alert'),			
			
			//one time use message start			
			
			"DEFAULT_BUYER_SMS_PENDING" 					=> sprintf(__('Hello %s, you are just one step away from placing your order, please complete your payment, to proceed.%sPowered by%swww.smsalert.co.in','sms-alert'), '[billing_first_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_ADMIN_SMS_CANCELLED" 					=> sprintf(__('%s Your order %s Rs. %s. is Cancelled.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '#[order_id]', '[order_amount]',PHP_EOL,PHP_EOL),
			"DEFAULT_ADMIN_SMS_PENDING" 					=> sprintf(__('%s Hello, %s is trying to place order %s value Rs. %s%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '[billing_first_name]', '#[order_id]', '[order_amount]',PHP_EOL,PHP_EOL),			
			"DEFAULT_ADMIN_SMS_ON_HOLD" 					=> sprintf(__('%s Your order %s Rs. %s. is On Hold Now.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '#[order_id]', '[order_amount]',PHP_EOL,PHP_EOL),
			"DEFAULT_ADMIN_SMS_COMPLETED" 					=> sprintf(__('%s Your order %s Rs. %s. is completed.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '#[order_id]', '[order_amount]',PHP_EOL,PHP_EOL),
			"DEFAULT_ADMIN_SMS_PROCESSING" 					=> sprintf(__('%s You have a new order %s for order value Rs. %s. Please check your admin dashboard for complete details.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '#[order_id]', '[order_amount]',PHP_EOL,PHP_EOL),
			"DEFAULT_BUYER_SMS_PROCESSING"  				=> sprintf(__('Hello %s, thank you for placing your order %s with %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[billing_first_name]', '#[order_id]', '[store_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_BUYER_SMS_COMPLETED" 					=> sprintf(__('Hello %s, your order %s with %s has been dispatched and shall deliver to you shortly. Thank you for Shopping with us.%sPowered by%swww.smsalert.co.in','sms-alert'), '[billing_first_name]', '#[order_id]', '[store_name]',PHP_EOL,PHP_EOL),			
			"DEFAULT_BUYER_SMS_ON_HOLD" 					=> sprintf(__('Hello %s, your order %s with %s has been put on hold, our team will contact you shortly with more details.%sPowered by%swww.smsalert.co.in','sms-alert'), '[billing_first_name]', '#[order_id]', '[store_name]',PHP_EOL,PHP_EOL),			
			"DEFAULT_BUYER_SMS_CANCELLED" 					=> sprintf(__('Hello %s, your order %s with %s has been cancelled due to some un-avoidable conditions. Sorry for the inconvenience caused.%sPowered by%swww.smsalert.co.in','sms-alert'), '[billing_first_name]', '#[order_id]', '[store_name]',PHP_EOL,PHP_EOL),			
			"DEFAULT_ADMIN_OUT_OF_STOCK_MSG" 				=> sprintf(__('%s Out Of Stock Alert For Product %s, current stock %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '[item_name]', '[item_qty]',PHP_EOL,PHP_EOL),
			"DEFAULT_ADMIN_LOW_STOCK_MSG" 					=> sprintf(__('%s Low Stock Alert For Product %s, current stock %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '[item_name]', '[item_qty]',PHP_EOL,PHP_EOL),
			"DEFAULT_AC_ADMIN_MESSAGE" 						=> sprintf(__('%s Product %s is left in cart by %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '[item_name]', '[name]', PHP_EOL, PHP_EOL),			
			"DEFAULT_AC_CUSTOMER_MESSAGE" 					=> sprintf(__('Hey %s, We noticed you could not complete your order. Click on the link below to place your order. Shop Now - %s%sPowered by%swww.smsalert.co.in','sms-alert'), '[name]', '[checkout_url]',PHP_EOL,PHP_EOL),
			"DEFAULT_AB_CART_CUSTOMER_MESSAGE" 				=> sprintf(__('Hey %s, We noticed you could not complete your order. Click on the link below to place your order. Shop Now - %s%sPowered by%swww.smsalert.co.in','sms-alert'), '[name]', '[checkout_url]',PHP_EOL,PHP_EOL),
			"DEFAULT_ADMIN_SMS_STATUS_CHANGED" 				=> sprintf(__('%s status of order %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '#[order_id]', '[order_status]',PHP_EOL,PHP_EOL),
			//one time use message end
			
			//not in use start			
			"OTP_INVALID_NO" 								=> sprintf(__('your verification code is %s. Only valid for %s min.','sms-alert'), '[otp]', '15'),
			"OTP_ADMIN_MESSAGE" 							=> sprintf(__('You have a new Order%sThe %s is now %s','sms-alert'), PHP_EOL, '[order_id]', '[order_status]'.PHP_EOL),
			"OTP_BUYER_MESSAGE" 							=> sprintf(__('Thanks for purchasing%sYour %s is now %sThank you','sms-alert'), PHP_EOL, '[order_id]', '[order_status]'.PHP_EOL),
			//not in use end
			
			"DEFAULT_BUYER_SMS_STATUS_CHANGED" 				=> sprintf(__('Hello %s, status of your order %s with %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[billing_first_name]', '#[order_id]', '[store_name]', '[order_status]',PHP_EOL,PHP_EOL),
			"DEFAULT_BUYER_NOTE" 							=> sprintf(__('Hello %s, a new note has been added to your order %s on %s: %s%sPowered by%swww.smsalert.co.in','sms-alert'), '[billing_first_name]', '#[order_id]:', '[shop_url]', '[note]',PHP_EOL,PHP_EOL),
			"DEFAULT_BUYER_OTP" 							=> sprintf(__('Your verification code for %s is %s%sPowered by%swww.smsalert.co.in','sms-alert'),'[shop_url]','[otp]',PHP_EOL,PHP_EOL),
			"OTP_SENT_PHONE" 								=> sprintf(__('A OTP (One Time Passcode) has been sent to %sphone%s . Please enter the OTP in the field below to verify your phone.','sms-alert'), '##', '##'),		
			"DEFAULT_WPAM_BUYER_SMS_STATUS_CHANGED" 		=>
			sprintf(__('Hello %s, status of your affiliate account %s with %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'),'[first_name]','[affiliate_id]','[store_name]','[affiliate_status]',PHP_EOL,PHP_EOL),
			//Review Request
			"DEFAULT_CUSTOMER_REVIEW_MESSAGE" 				=>
			sprintf(__('Hi %s, thank you for your recent order on %s. Can you take 1 minute to leave a review about your experience with us? %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[first_name]', '[store_name]', 'https://www.google.com/search?q=[shop_url]',PHP_EOL,PHP_EOL),
			//New User Approve
			"DEFAULT_NEW_USER_APPROVED" 					=>
			sprintf(__('Dear %s, your account with %s has been approved.%sPowered by%swww.smsalert.co.in','sms-alert'), '[username]','[store_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_NEW_USER_REJECTED" 					=>
			sprintf(__('Dear %s, your account with %s has been rejected.%sPowered by%swww.smsalert.co.in','sms-alert'), '[username]','[store_name]',PHP_EOL,PHP_EOL),
			//LearnPress
			"DEFAULT_LPRESS_BUYER_SMS_STATUS_CHANGED" 		=>
			sprintf(__('Hello %s, status of your %s with %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[username]', '[order_id]', '[store_name]', '[order_status]',PHP_EOL,PHP_EOL),
			"DEFAULT_LPRESS_ADMIN_SMS_STATUS_CHANGED"		=>
			sprintf(__('%s status of order %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '#[order_id]', '[order_status]',PHP_EOL,PHP_EOL),
			//Notify Me
			"DEFAULT_BACK_IN_STOCK_CUST_MSG" 				=>
			sprintf(__('Hello, %s is now available, you can order it on %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[item_name]', '[shop_url]',PHP_EOL,PHP_EOL),
			"DEFAULT_BACK_IN_STOCK_SUBSCRIBE_MSG" 			=>
			sprintf(__('We have noted your request and we will notify you as soon as %s is available for order with us.%sPowered by%swww.smsalert.co.in','sms-alert'), '[item_name]',PHP_EOL,PHP_EOL),
			//Event Manager
			"DEFAULT_EM_CUSTOMER_MESSAGE"					=>
			sprintf(__('Hello %s, status of your booking %s%s with %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[#_BOOKINGNAME]', '[#_BOOKINGID]', '[#_EVENTNAME]', '[store_name]','[#_BOOKINGSTATUS]',PHP_EOL,PHP_EOL),
			"DEFAULT_EM_ADMIN_MESSAGE"						=>
			sprintf(__('%s: status of booking %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]','[#_BOOKINGID]','[#_BOOKINGSTATUS]',PHP_EOL,PHP_EOL),		
			//EDD
			"DEFAULT_EDD_BUYER_SMS_STATUS_CHANGED" 			=>
			sprintf(__('Hello %s, status of your order %s with %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[first_name]', '[order_id]', '[store_name]', '[order_status]',PHP_EOL,PHP_EOL),
			"DEFAULT_EDD_ADMIN_SMS_STATUS_CHANGED"			=>
			sprintf(__('%s: status of order %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]', '#[order_id]', '[order_status]',PHP_EOL,PHP_EOL),
			//Delivery Driver
			"DEFAULT_DELIVERY_DRIVER_MESSAGE"				=>
			sprintf(__('%s: Hello %s, you have been assigned a new delivery for %s%s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]', '[first_name]', '[item_name]', '[item_name_qty]',PHP_EOL,PHP_EOL),
			
			//Ninja
			"DEFAULT_NINJA_ADMIN_MESSAGE" 					=>
			sprintf(__('%s: Dear %s, new Contact from %s on %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]','admin','[name]','[shop_url]',PHP_EOL,PHP_EOL),			
			"DEFAULT_NINJA_CUSTOMER_MESSAGE" =>
			sprintf(__('Hello %s, Thank you for submitting on %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[name]','[store_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_WPAM_ADMIN_SMS_STATUS_CHANGED" 		=> sprintf(__('%s status of affiliate %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '#[affiliate_id]', '[affiliate_status]',PHP_EOL,PHP_EOL),			
			"DEFAULT_WPAM_BUYER_SMS_TRANS_STATUS_CHANGED" 	=> sprintf(__('Hello %s,commission has been %s for %s to your affiliate account %s against order %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[first_name]', '[transaction_type]', '[commission_amt]', '[affiliate_id]', '#[order_id]',PHP_EOL,PHP_EOL),
			"DEFAULT_WPAM_ADMIN_SMS_TRANS_STATUS_CHANGED" 	=> sprintf(__('%s commission has been %s for %s to affiliate account %s against order %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '[transaction_type]', '[commission_amt]', '[affiliate_id]', '#[order_id]',PHP_EOL,PHP_EOL),
			"DEFAULT_ADMIN_NEW_USER_REGISTER"     			=> sprintf(__('%s: A new %s has signed up on %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:','[username]', '[email]',PHP_EOL,PHP_EOL),
			
			"PHONE_NOT_FOUND" 								=> __('Sorry, but you do not have a registered phone number.','sms-alert'),			
			"PHONE_MISMATCH" 								=> __('The phone number OTP was sent to and the phone number in contact submission do not match.','sms-alert'),
			"DEFAULT_USER_COURSE_ENROLL" 					=> sprintf(__('Congratulation %s, you have enrolled course %s with %s%sPowered by%swww.smsalert.co.in','sms-alert'), '[username]', '[course_name]','[store_name]',PHP_EOL,PHP_EOL),			
			"DEFAULT_NEW_USER_REGISTER" 					=> sprintf(__('Hello %s, Thank you for registering with %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[username]', '[store_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_WARRANTY_STATUS_CHANGED" 				=> sprintf(__('Hello %s, status of your RMA no. %s with %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[billing_first_name]', '[rma_number]', '[store_name]', '[rma_status]',PHP_EOL,PHP_EOL),
			"DEFAULT_ADMIN_COURSE_FINISHED" 				=> sprintf(__('%s Hi Admin %s has finished course %s%sPowered by%swww.smsalert.co.in','sms-alert'),'[store_name]:', '[username]', '[course_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_USER_COURSE_FINISHED" 					=> sprintf(__('Congratulation you have finished course %s with %s%sPowered by%swww.smsalert.co.in','sms-alert'), '[course_name]','[store_name]',PHP_EOL,PHP_EOL),	
			"DEFAULT_ADMIN_NEW_TEACHER_REGISTER" 			=> sprintf(__('%s Hi admin, an instructor %s has been joined.%sPowered by%swww.smsalert.co.in','sms-alert'),'[store_name]:', '[username]',PHP_EOL,PHP_EOL),
			"DEFAULT_ADMIN_COURSE_ENROLL" 					=> sprintf(__('%s Hi Admin %s has enrolled course - %s%sPowered by%swww.smsalert.co.in','sms-alert'),'[store_name]:', '[username]', '[course_name]',PHP_EOL,PHP_EOL),			
			"DEFAULT_NEW_TEACHER_REGISTER" 					=> sprintf(__('Congratulation %s, you have become an instructor with %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[username]','[store_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_BOOKING_CALENDAR_CUSTOMER" 			=> sprintf(__('Congratulation %s, you have become an instructor with %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[username]','[store_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_BOOKING_CALENDAR_CUSTOMER_PENDING" 	=> sprintf(__('Dear %s, thank you for scheduling your booking with %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[name]', '[store_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_BOOKING_CALENDAR_CUSTOMER_APPROVED" 	=> sprintf(__('Hello %s, status of your order with %s has been changed to confirmed.%sPowered by%swww.smsalert.co.in','sms-alert'), '[name]', '[store_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_BOOKING_CALENDAR_CUSTOMER_TRASH" 		=> sprintf(__('Hello %s, status of your order with %s has been changed to rejected.%sPowered by%swww.smsalert.co.in','sms-alert'), '[name]','[store_name]',PHP_EOL,PHP_EOL),			
			"DEFAULT_BOOKING_CALENDAR_ADMIN" 				=> sprintf(__('Congratulation %s, you have become an instructor with %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[username]','[store_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_BOOKING_CALENDAR_ADMIN_PENDING" 		=> sprintf(__('You have a new booking from %s for %s %s. Please check admin dashboard for complete details.%sPowered by%swww.smsalert.co.in','sms-alert'), '[name]', '[date]','[store_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_BOOKING_CALENDAR_ADMIN_APPROVED" 		=> sprintf(__('%s: status of booking %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]', '[name]','confirmed',PHP_EOL,PHP_EOL),
			"DEFAULT_BOOKING_CALENDAR_ADMIN_TRASH" 			=> sprintf(__('%s: status of booking %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]', '[name]','rejected',PHP_EOL,PHP_EOL),
			"DEFAULT_CUST_SUBS_RENEWAL_MSG" 				=> sprintf(__('Hello %s, thank you for renew your subscription %s with %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[billing_first_name]', '#[subscription_id]', '[store_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_ADMIN_SUBS_CREATE_MSG" 			=> sprintf(__('%s You have a new subscription %s for subscription value Rs. %s. Please check your admin dashboard for complete details.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '#[subscription_id]', '[order_amount]',PHP_EOL,PHP_EOL),
			"DEFAULT_ADMIN_SUBS_STATUS_CHANGE_MSG"		=> sprintf(__('%s status of order %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '#[subscription_id]', '[subscription_status]',PHP_EOL,PHP_EOL),
			"DEFAULT_ADMIN_SUBS_RENEWAL_MSG" 			=> sprintf(__('%s You have a renew subscription %s for subscription value Rs. %s. Please check your admin dashboard for complete details.%sPowered by%swww.smsalert.co.in','sms-alert'), '[store_name]:', '#[subscription_id]', '[order_amount]',PHP_EOL,PHP_EOL),
			"DEFAULT_CUST_SUBS_CREATE_MSG" 				=> sprintf(__('Hello %s, thank you for placing your subscription %s with %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[billing_first_name]', '#[subscription_id]', '[store_name]',PHP_EOL,PHP_EOL),
			"DEFAULT_CUST_SUBS_STATUS_CHANGE_MSG"		=> sprintf(__('Hello %s, status of your order %s with %s has been changed to %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[billing_first_name]', '#[subscription_id]', '[store_name]', '[subscription_status]',PHP_EOL,PHP_EOL),
			"DEFAULT_CUST_SUBS_RENEWAL_MSG" 			=> sprintf(__('Hello %s, thank you for renew your subscription %s with %s.%sPowered by%swww.smsalert.co.in','sms-alert'), '[billing_first_name]', '#[subscription_id]', '[store_name]',PHP_EOL,PHP_EOL),

			/*translation required*/
		)));
	}

	public static function showMessage($message , $data=array())
	{
		$displayMessage = "";
		$messages = explode(" ",$message);
		$msg = unserialize(SALRT_MESSAGES);
		//return __($msg[$message],'sms-alert');
		return (!empty($msg[$message]) ? $msg[$message] : '');
		/* foreach ($messages as $message)
		{
			if(!SmsAlertUtility::isBlank($message))
			{
				//$formatMessage = constant( "self::".$message );
				$formatMessage = $msg[$message];
			    foreach($data as $key => $value)
			    {
			        $formatMessage = str_replace("{{" . $key . "}}", $value ,$formatMessage);
			    }
			    $displayMessage.=$formatMessage;
			}
		}
	    return $displayMessage; */
	}
}
new SmsAlertMessages;