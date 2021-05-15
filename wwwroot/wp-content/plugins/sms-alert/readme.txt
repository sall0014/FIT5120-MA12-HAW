﻿=== SMS Alert Order Notifications - WooCommerce ===

Contributors: cozyvision1
Tags: order notification, order SMS, woocommerce sms integration, sms plugin, mobile verification
Requires at least: 4.6
Tested up to: 5.7
Stable tag: 3.4.5
Requires PHP: 5.6
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin for sending SMS notification after placing orders using WooCommerce

== Description ==

This is a WooCommerce add-on. By Using this plugin admin and buyer can get notification about their order via sms using SMS Alert.

The WooCommerce Order SMS Notification plugin for WordPress is very useful, when you want to get notified via SMS after placing an order. Buyer and seller both can get SMS notification after an order is placed. SMS notification options can be customized in the admin panel very easily.

https://youtu.be/nSoXZBWEG5k

= SMSAlert - WooCommerce (Key Features) =

> + OTP for order confirmation(with option to enable OTP only for COD orders)
> + OTP verification for registration
> + Login with OTP
> + Reset password with OTP
> + OTP verification for login(option to enable OTP only for selected roles)
> + SMS to Customer and Admin on new user registration/signup
> + Admin/Post Author can get Order SMS notifications
> + Buyer can get order sms notifications supports custom template
> + Sending order Details ( order no, order status, order items and order amount ) in SMS text
> + Different SMS template corresponding to different Order Status
> + Directly contact with buyer via SMS through order notes, and custom sms available on order detail page
> + All order status supported(Pending, On Hold, Completed, Cancelled)
> + Block multiple user registration with same mobile number
> + Supports wordpress multisite
> + Custom Low balance alert
> + Option to disable sending OTP to a particular after n resends
> + Daily SMS balance on Email
> + Sync Customers to Group on [www.smsalert.co.in](https://www.smsalert.co.in)
> + Auto Shorten URL
> + Low Stock Alert to admin
> + Out of Stock Alert to admin
> + Back in Stock notifier
> + Abandoned Cart Reminder

= Compatibility =

👉 [Sequential Order Numbers Pro](https://woocommerce.com/products/sequential-order-numbers-pro/)
👉 [WooCommerce Order Status Manager](https://woocommerce.com/products/woocommerce-order-status-manager/)
👉 [Admin Custom Order Fields](https://woocommerce.com/products/admin-custom-order-fields/)
👉 [Shipment Tracking](https://woocommerce.com/products/shipment-tracking/)
👉 [Advanced Shipment Tracking for WooCommerce](https://wordpress.org/plugins/woo-advanced-shipment-tracking/)
👉 [Aftership - WooCommerce Tracking](https://wordpress.org/plugins/aftership-woocommerce-tracking/)
👉 [Ultimate Member](https://wordpress.org/plugins/ultimate-member/)
👉 [Pie Register](https://wordpress.org/plugins/pie-register/)
👉 [WP-Members Membership Plugin](https://wordpress.org/plugins/wp-members/)
👉 [User Registration](https://wordpress.org/plugins/user-registration/)
👉 [Easy Registration Forms](https://wordpress.org/plugins/easy-registration-forms/)
👉 [Profile Builder](https://wordpress.org/plugins/profile-builder/)
👉 [Dokan Multivendor Marketplace](https://wordpress.org/plugins/dokan-lite/)
👉 [WC Marketplace](https://wordpress.org/plugins/dc-woocommerce-multi-vendor/)
👉 [WooCommerce PDF Invoices & Packing Slips](https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/) to send invoice on SMS
👉 [Claim GST for Woocommerce](https://wordpress.org/plugins/claim-gst/) for Input tax credit
👉 [Order Delivery Date for WooCommerce](https://wordpress.org/plugins/order-delivery-date-for-woocommerce/)
👉 [WooCommerce Multi-Step Checkout](https://wordpress.org/plugins/wp-multi-step-checkout/)
👉 [WooCommerce Serial Numbers](https://wordpress.org/plugins/wc-serial-numbers/)

= Integrations =

👨 [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) to send notification to customer and admins, and verify mobile number through OTP
👨 [Ninja Forms](https://wordpress.org/plugins/ninja-forms/) to send notification to customer and admins, and verify mobile number through OTP
👨 [WPForms](https://wordpress.org/plugins/wpforms-lite/) to send notification to customer and admins, and verify mobile number through OTP
👨 [Gravity Forms](https://www.gravityforms.com/) to send notification to customer and admins
👨 [Returns and Warranty Requests](https://woocommerce.com/products/warranty-requests/) to send RMA status update to customer
👨 [Easy Digital Downloads](https://wordpress.org/plugins/easy-digital-downloads/) to send notification to customer
👨 [Affiliates Manager](https://wordpress.org/plugins/affiliates-manager/) to send notification to Affiliates and admin
👨 [WooCommerce Bookings](https://woocommerce.com/products/woocommerce-bookings/) to send booking confirmation to customers and admin
👨 [Product Vendors](https://woocommerce.com/products/product-vendors/)
👨 [Booking Calendar](https://wordpress.org/plugins/booking/) to send booking confirmation to customers and admin
👨 [LearnPress – WordPress LMS Plugin](https://wordpress.org/plugins/learnpress/) to send notifications to student and admin
👨 [Events Manager](https://wordpress.org/plugins/events-manager/) to send event booking confirmation to customer and admin
👨 [Delivery Drivers for WooCommerce](https://wordpress.org/plugins/delivery-drivers-for-woocommerce/)
👨 [New User Approve](https://wordpress.org/plugins/new-user-approve)
👨 [Woocommerce Simple Auctions](https://codecanyon.net/item/woocommerce-simple-auctions-wordpress-auctions/6811382)

== Frequently Asked Questions ==

= Can i integrate my own sms gateway? =

There is no provision to integrate any other SMS Gateway, we only support [SMS Alert](http://www.smsalert.co.in/) SMS Gateway.

= How do i change Sender id? =

You can request the sender id after login to your [SMS Alert](http://www.smsalert.co.in/) account, from manage sender id.

Sender id is only available for transactional account.

= I signed up for a demo account, but not received any test sms =

As per TRAI Guidelines promotional sms can be sent only from 9 am to 9 pm, please test during this period only, also check if your number is not registered in NDNC registry.

If still you face any issues, please [contact](https://wordpress.org/support/plugin/sms-alert) our support team.

= I am unable to login to my wordpress admin =

This can happen in two cases like you do not have sms credits in your sms alert account, or your admin profile has some other number registered, for both cases you can rename the plugin directory in your wordpress plugin directory via FTP, to disable the plugin

= Which all countries do you support sms? =

Please check complete list of supported countries on our [website](https://www.smsalert.co.in)

= Can i send sms to multiple countries from one account? =

Yes, you can send sms to multiple countries, by default your account is configured to send SMS to only one country, you can request to allow additional countries for your account through email on support@cozyvision.com.

= How can i use my custom variables in sms templates? =

The plugin supports custom order post meta, if your post meta key is '_my_custom_key', then you can access it in sms templates as [my_custom_key]

= Can i extend the functionality of this plugin? =

Sure, you can use our below hooks.

**To Send SMS**

~~~~
do_action('sa_send_sms', '918010551055', 'Here is the sms.');
~~~~

**To Modify Parameters before sending any SMS**

~~~~
function modify_sms_text($params)
{    
    //do your stuff here
	return $params;    
}
add_filter('sa_before_send_sms', 'modify_sms_text');
~~~~

**To get SMS Alert Service Response after Send SMS**

~~~~
function get_smsalert_response($params)
{ 
	//do your stuff here
	return $params;
}
add_filter('sa_after_send_sms', 'get_smsalert_response');
~~~~

**Woocommerce before Send SMS**

~~~~
function public static function modify_sms_text($content, $wc_order_id)
{ 
	//do your stuff here
	return $content;
}
add_filter('sa_wc_order_sms_before_send', 'modify_sms_text', 1, 2);
~~~~

= Can you customise the plugin for me? =

Please use wordpress [support forum](https://wordpress.org/support/plugin/sms-alert) for new feature request, our development team may consider it in future updates. Please note we do not have any plans to develop any integrations for any paid plugins, if still you need it someone like you must sponser the update :-)

= Where can i find the plugin documenation? =

Please refer [here](https://kb.smsalert.co.in/wordpress) for plugin usage guide.

== Screenshots ==

1. OTP popup - Login, Registration, Checkout, Contact Form 7.
2. Login with OTP.
3. General Settings - Login with your www.smsalert.co.in username and password.
4. OTP Settings
5. Customer Templates - Set sms templates for every order status, these will be sent to the customers.
6. Admin SMS Templates - Set sms templates that admin will receive, set admin mobile number from advanced settings.
7. Advanced Settings - Enable or disable daily balance alert, low balance alert, admin mobile number, and many other advanced options.
8. Custom SMS on Order detail page - You can send custom personalised sms to the customer directly from order detail page from your admin panel, this is very useful in case you wih to update customer in case of any unplanned event, like delay in delivery, order disputes and claims, etc.
9. Returns and Warranty Requests - Send SMS to customer and admin when a new warranty request is placed, or warranty request status changes.
10. Gravity Forms - Send sms to customer and admin, whenever the form is submitted.
11. Contact Form 7 - Visitor & Admin Message, SMS OTP Verification.
12. Easy Digital Downloads - Notification to Customer and Admin on various order status's.
13. Woocommerce Bookings - Customer Templates
14. Woocommerce Bookings - Admin Templates

== Changelog ==

= 3.3.7 =
* Country code selector on login with OTP and registration form
* Mask phone number on forgot password
* OTP for Delivery drivers for delivery confirmation
* Integration with Easy Registration Forms
* Integration with New User Approve
* Integration with Product Vendors
* Integration with Profile Builder
* Integration with AST Tracking per item
* New: Request Review(schedule sms)
* Send SMS action now supports schedule SMS
* Shortcode support for OTP Verification
* Removed depricated function calls from jquery
* New improved Admin UI
* Bugfix: Same mobile number was not getting blocked on registration from checkout page

= 3.3.8 =
* Feature: Option to Reset All Settings and Templates of Plugin
* Added Provision for upgrade script in order to update plugin database
* Enhancement: Better number handling (remove leading zeros from number, and spaces)
* Bugfix: Duplicate mobile number signup, number with and without country code are now considered same
* Bugfix: Ultimate Member country code selector css fix
* Feature: OTP Verification after order is placed
* Bugfix: Delivery driver for woocommerce was unable to return the order when OTP was enabled
* Translation plugins compatibility fix
* Compatibility check with latest woocommerce version
* Bugfix: Review message were going for sub-orders for multi vendor plugins

= 3.3.9 =
* New: Integration with WooCommerce Serial Numbers
* Enhancement: added cartbounty pro cart link variable in sms
* Enhancement: moved post otp verification link to top of page
* Bugfix: country code selector was not working on mobile devices
* Enhancement: added checkout url for pending and failed orders
* Compatibility check with latest woocommerce version

= 3.4.0 =
* New: Integration with WPForms
* New: Abandoned Cart Recovery
* Enhancement: Added translation support for javascript strings
* Enhancement: OTP Verification shortcode now validates form before sending otp
* Enhancement: Better number handling for intelliinput
* Enhancement: design improvements for login with otp button
* Enhancement: added support for country code selector for Contact form 7, Easy Registration
* Bugfix: Intelliinput was adding country code multiple times on some themes
* Compatibility fixes for latest Wordpress and woocommerce version

= 3.4.1 =
* Bugfix: OTP Popup design issue for mobile devices
* Enhancement: Better design, and number handling for Notify me when available
* Bugfix: Login with OTP compatibility fix with older wordpress versions
* Enhancement: Login with OTP number validation
* Country code settings is now always visible in advanced settings

= 3.4.2 =
* Bugfix: Login with OTP - custom redirect
* Bugfix: checkout button not visible with specific settings
* Bugfix: Thank you message was not visible with default checkout
* Bugfix: WP affiliate sms was being sent even when it was not active
* Enhancement: validation before sending otp on default checkout form
* Enhancement: Back in Stock, now shows backorders on products page.
* Enhancement: Max OTP limit error handling
* Enhancement: login with otp validation on blank submit
* New: Integration with [Woocommerce Simple Auctions](https://codecanyon.net/item/woocommerce-simple-auctions-wordpress-auctions/6811382)
* compatibility check with latest woocommerce version
* Code cleanup

= 3.4.4 =
* Bugfix: Woocommerce order variables were not replacing when OTP for checkout was disabled
* Bugfix: review and abondoned cart options were not visible if turned off

= 3.4.5 =
* Bugfix: validate before sending otp, shows messages but otp message triggers
* Bugfix: Post order verification - otp for registration was not working
* Bugfix: Post order verification - Blank div shown after placing order
* Bugfix: on deleting an item from cart, cart was shown as recovered
* Bugfix: updation time after placing order
* Bugfix: Prevent double click on ninja sending otp
* Bugfix: Ninja backend same form field names do not allow to save phone field
* Enhancement: added support for wpforms pro version
* Enhancement: better menu handling to support more options at backend
* Enhancement: if billing phone is not present then customer can place order without OTP
* Enhancement: contact form validation with file type field
* Enhancement: compatibility changes for wootsify theme
* Enhancement: register and login button css not compatible for more wp themes
* Enhancement: By default, mobile verificaton in ninja form is now disabled
* Enhancement: Common function created for replacing store_name and shop url
* Enhancement: backend validation when otp for selected payment gateway is checked, but no payment gateway is selected
* Enhancement: backend validation if user enables otp verification for admin and admin profile does not have any contact number
* Enhancement: Low stock message will now go to vendors also
* Enhancement: Updated default plugin messages due to upcoming DLT changes for India

== Support ==

Since this plugin is dependent on www.smsalert.co.in, we provide 24X7 email support for this plugin via support@cozyvision.com. For new feature requests please use wordpress [support forum](https://wordpress.org/support/plugin/sms-alert).

== Translations ==

* English - default