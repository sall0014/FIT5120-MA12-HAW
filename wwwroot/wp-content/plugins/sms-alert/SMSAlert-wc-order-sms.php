<?php
/*
 * Plugin Name: SMSAlert - WooCommerce
 * Plugin URI: https://wordpress.org/plugins/sms-alert/
 * Description: This is a WooCommerce add-on. By Using this plugin admin and buyer can get notification after placing order via sms using SMS Alert.
 * Version: 3.4.5
 * Author: Cozy Vision Technologies Pvt. Ltd.
 * Author URI: https://www.smsalert.co.in
 * WC requires at least: 2.0.0
 * WC tested up to: 5.1.0
 * Text Domain: sms-alert
 * License: GPLv2
 */

/**
 *
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;
if (!defined('SMSALERT_TEXT_DOMAIN')) define( 'SMSALERT_TEXT_DOMAIN', 'sms-alert' );
if (!defined('SMSALERT_PLUGIN_NAME')) define( 'SMSALERT_PLUGIN_NAME', 'SMS Alert Order Notifications – WooCommerce' );
if (!defined('SMSALERT_ABANDONED')) define( 'SMSALERT_ABANDONED', 'smsalert_abandoned' );
if (!defined('SMSALERT_PLUGIN_NAME_SLUG')) define( 'SMSALERT_PLUGIN_NAME_SLUG', 'sms-alert' );
if (!defined('SA_CART_TABLE_NAME')) define( 'SA_CART_TABLE_NAME', 'sa_captured_wc_fields' );
if (!defined('CART_CRON_INTERVAL')) define( 'CART_CRON_INTERVAL', 10 );//run ab cart cron every 10 min
//In minutes. Defines the interval at which msg function is fired
if (!defined('CART_STILL_SHOPPING')) define( 'CART_STILL_SHOPPING', 10 ); //In minutes. Defines the time period after which an msg notice will be sent and the cart is presumed abandoned
if (!defined('CART_NEW_STATUS_NOTICE')) define( 'CART_NEW_STATUS_NOTICE', 240 ); //Defining time in minutes how long New status is shown in the table

if (!defined('CART_ENCRYPTION_KEY')) define('CART_ENCRYPTION_KEY', 'SgVkYp3s6v9y$B&M)H+MbQeThWmZq4t9' );

function smsalert_sanitize_array($arr)
{
	global $wp_version;
	$older_version = ($wp_version<'4.7') ? true:false;
	$result = array();
	foreach ($arr as $key => $val)
	{
		$result[$key] = is_array($val) ? smsalert_sanitize_array($val) : (($older_version) ? sanitize_text_field($val) : sanitize_textarea_field($val));
	}
	return $result;
}

function create_smsalert_cookie($cookie_key,$cookie_value)
{
	ob_start();
	setcookie($cookie_key,$cookie_value, time()+(15 * 60));
	ob_get_clean();
}

function clear_smsalert_cookie($cookie_key)
{
	if(isset($_COOKIE[$cookie_key])){
		unset($_COOKIE[$cookie_key]);
		setcookie( $cookie_key, '', time() - ( 15 * 60 ) );
	}
}

function get_smsalert_cookie($cookie_key)
{
	if(!isset($_COOKIE[$cookie_key])) {
	  return false;
	} else {
	  return $_COOKIE[$cookie_key];
	}
}

function smsalert_get_option( $option, $section, $default = '' ) {
    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }
    return $default;
}

function get_smsalert_template($filepath,$datas)
{
		ob_start();
		extract($datas);
		include(plugin_dir_path( __DIR__ ).'sms-alert/'.$filepath);
		return ob_get_clean();
}

class smsalert_WC_Order_SMS {

    /**
     * Constructor for the smsalert_WC_Order_SMS class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses is_admin()
     * @uses add_action()
     */
	public function __construct() {

		// Instantiate necessary class
        $this->instantiate();
		add_action( 'init', array($this, 'register_hook_send_sms'));

		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'buyer_notification_update_order_meta' ) );
		add_action( 'woocommerce_order_status_changed', array( 'WooCommerceCheckOutForm', 'trigger_after_order_place' ), 10, 3 );
		add_action('woocommerce_new_order', array( $this, 'sa_wc_order_place'),10,1);
		add_filter( 'sa_wc_order_sms_customer_before_send' , array( 'WooCommerceCheckOutForm','pharse_sms_body'),10,2 );
		add_filter( 'sa_wc_order_sms_admin_before_send' , array( 'WooCommerceCheckOutForm','pharse_sms_body'),10,2 );

		if ( is_plugin_active( 'gravityforms-master/gravityforms.php' ) || is_plugin_active('gravityforms/gravityforms.php' ))
		{
			require_once 'handler/forms/gravity-form.php';
		}

		require_once 'helper/formlist.php';
		require_once 'views/common-elements.php';
		require_once 'handler/forms/FormInterface.php';
		require_once 'handler/smsalert_form_handler.php';
		require_once 'helper/shortcode.php';

		if(is_admin())
		{
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta_link' ), 10, 4 );
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links') );
		}

		/*code to notify for daily balance begins */
		add_action ('smsalert_balance_notify',array($this, 'background_task'));
		self::sa_sync_grp_action();
		add_filter('sa_before_send_sms',array($this, 'replace_token_name'), 100,1);// add on 18/03/2021
	}

    /**
     * Instantiate necessary Class
     * @return void
     */
    function instantiate() {
		spl_autoload_register( array($this, 'smsalert_sms_autoload') );
        new smsalert_Setting_Options();
    }

	/**
	 * Autoload class files on demand
	 *
	 * @param string $class requested class name
	 */
	function smsalert_sms_autoload( $class ) {

		require_once 'handler/smsalert_logic_interface.php';
		require_once 'handler/smsalert_phone_logic.php';
		require_once 'helper/sessionVars.php';
		require_once 'helper/utility.php';
		require_once 'helper/constants.php';
		require_once 'helper/messages.php';
		require_once 'helper/curl.php';

		if ( stripos( $class, 'smsalert_' ) !== false ) {

			$class_name = str_replace( array('smsalert_', '_'), array('', '-'), $class );
			$filename = dirname( __FILE__ ) . '/classes/' . strtolower( $class_name ) . '.php';

			if ( file_exists( $filename ) ) {
				require_once $filename;
			}
		}
	}

    /**
     * Initializes the SMSAlert_WC_Order_SMS() class
     *
     * Checks for an existing SMSAlert_WC_Order_SMS() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new SMSAlert_WC_Order_SMS();
        }
        return $instance;
    }

	function fn_sa_send_sms($number, $content, $schedule=NULL)
	{
		$obj				= array();
		$obj['number'] 		= $number;
		$obj['sms_body'] 	= $content;
		$obj['schedule'] 	= $schedule;
		$response 			= SmsAlertcURLOTP::sendsms($obj);
		return $response;
	}
	
	//replace store_name 18/03/2021	
	public function replace_token_name($fields){
	
		$search = array(
			'[store_name]',
			'[shop_url]',
		);
		
		$replace = array(
			get_bloginfo(),
			get_site_url()
		);
	
		$fields['text']  = str_replace($search, $replace, $fields['text']);
		return $fields;
	}

	function register_hook_send_sms()
	{
		add_action( 'sa_send_sms', array($this, 'fn_sa_send_sms'), 10, 3 );
	}

    public function admin_enqueue_scripts() {
		wp_enqueue_style( 'admin-smsalert-styles', plugins_url( 'css/admin.css', __FILE__ ), array(),SmsAlertConstants::SA_VERSION );
        wp_enqueue_script( 'admin-smsalert-scripts', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), SmsAlertConstants::SA_VERSION, true );
		wp_enqueue_script( 'admin-smsalert-taggedinput', plugins_url( 'js/tagged-input.js', __FILE__ ), array( 'jquery' ), SmsAlertConstants::SA_VERSION, false );

        wp_localize_script( 'admin-smsalert-scripts', 'smsalert', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ) );
    }

    public function plugin_row_meta_link( $plugin_meta, $plugin_file, $plugin_data, $status )
	{
        if( isset( $plugin_data['slug'] ) && ( $plugin_data['slug'] == 'sms-alert' ) && ! defined( 'smsalert_DIR' ) ) {
			$plugin_meta[] = '<a href="http://kb.smsalert.co.in/wordpress" target="_blank">'.__('Docs','sms-alert').'</a>';
			$plugin_meta[] = '<a href="https://wordpress.org/support/plugin/sms-alert/reviews/#postform" target="_blank" class="wc-rating-link">★★★★★</a>';
        }
        return $plugin_meta;
    }

	function add_action_links ( $links ) {
		$links[] = sprintf('<a href="%s">Settings</a>', admin_url('admin.php?page=sms-alert') );
		return $links;
	}

	//return only single credit i.e onlyone route(transactional credit)
	static function only_credit(){
		$trans_credit =array();
		$credits = json_decode(SmsAlertcURLOTP::get_credits(),true);   //credit json
		if(is_array($credits['description']) && array_key_exists('routes', $credits['description']))
		{
			foreach($credits['description']['routes'] as $credit){
				//if($credit['route']=='transactional'){
					$trans_credit[] = $credit['credits'];
				//}
			}
		}
		return $trans_credit;
	 }

	static function run_on_activate()
	{
		//add activation date
		if(!get_option('smsalert_activation_date'))
		{
			add_option('smsalert_activation_date',date('Y-m-d'));
		}
		if( !wp_next_scheduled( 'smsalert_balance_notify' ) )
		{
			wp_schedule_event( time(), 'hourly', 'smsalert_balance_notify');
		}
		self::sa_cart_activate();
	}

	public static function sa_cart_activate() {

		// Creating table
		global $wpdb, $table_name;

		$table_name = $wpdb->prefix . SA_CART_TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id BIGINT(20) NOT NULL AUTO_INCREMENT,
			name VARCHAR(60),
			surname VARCHAR(60),
			email VARCHAR(100),
			phone VARCHAR(20),
			location VARCHAR(100),
			cart_contents LONGTEXT,
			cart_total DECIMAL(10,2),
			currency VARCHAR(10),
			time DATETIME DEFAULT '0000-00-00 00:00:00',
			session_id VARCHAR(60),
			msg_sent TINYINT NOT NULL DEFAULT 0,
			recovered TINYINT NOT NULL DEFAULT 0,
			other_fields LONGTEXT,
			PRIMARY KEY (id)
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta( $sql );

		//Resets table Auto increment index to 1
		$sql = "ALTER TABLE $table_name AUTO_INCREMENT = 1";
		dbDelta( $sql );

		$ab_cart_fc_captured_abandoned_cart_count = get_option('ab_cart_fc_captured_abandoned_cart_count');
		if($ab_cart_fc_captured_abandoned_cart_count){
			update_option('cart_captured_abandoned_cart_count', $ab_cart_fc_captured_abandoned_cart_count);
		}
		delete_option('ab_cart_fc_captured_abandoned_cart_count');

		$user_settings_notification_frequency = smsalert_get_option('customer_notify','smsalert_abandoned_cart','on');

		if($user_settings_notification_frequency == 'off'){ //If SMS notifications have been disabled, we disable cron job
			wp_clear_scheduled_hook( 'ab_cart_notification_sendsms_hook' );
		}else{
			if (! wp_next_scheduled ( 'ab_cart_notification_sendsms_hook' )){
				wp_schedule_event(time(), 'sendsms_interval', 'ab_cart_notification_sendsms_hook');
			}
		}
	}

	static function run_on_deactivate()
	{
		wp_clear_scheduled_hook('smsalert_balance_notify');
	}

	static function run_on_uninstall(){
		//Deletes database table and all it's data on uninstall
		global $wpdb;

		$main_table = $wpdb->prefix . "sa_captured_wc_fields";

		$wpdb->query( "DROP TABLE IF EXISTS $main_table" );

		//Removing Custom options created with the plugin
		delete_option( 'cart_captured_abandoned_cart_count' );
	}

	function background_task()
	{
		$low_bal_alert 		= smsalert_get_option( 'low_bal_alert', 'smsalert_general', 'off');
		$daily_bal_alert 	= smsalert_get_option( 'daily_bal_alert', 'smsalert_general', 'off');
		$user_authorize 	= new smsalert_Setting_Options();
		$islogged 			= $user_authorize->isUserAuthorised();
		$auto_sync 			= smsalert_get_option( 'auto_sync', 'smsalert_general', 'off');
		if($islogged == true)
		{
			if($auto_sync == 'on')
			{
				self::sync_customers();
			}
		}
		if($low_bal_alert == 'on'){self::send_smsalert_balance();}
		if($daily_bal_alert == 'on'){self::daily_email_alert();}

	}

	function sa_sync_grp_action()
	{
		if(array_key_exists('option', $_GET) && $_GET['option'])
		{
			switch (trim($_GET['option']))
			{
				case 'smsalert-group-sync':
				self::sync_customers();
				exit();
				break;
			}
		}
	}

	static function sync_customers()
	{
		$group_name 	= smsalert_get_option( 'group_auto_sync', 'smsalert_general', '');
		$update_id 		= smsalert_get_option( 'last_sync_userId','smsalert_sync','');
		$username 		= smsalert_get_option( 'smsalert_name', 'smsalert_gateway' );
		$password 		= smsalert_get_option( 'smsalert_password', 'smsalert_gateway' );
		if(empty($group_name))
			return;

		$update_id 		= ($update_id!='') ? $update_id : 0;
		global $wpdb;

		$sql 			= $wpdb->prepare(
		"SELECT ID FROM {$wpdb->users} WHERE {$wpdb->users}.ID > %d order by ID asc limit 100",
		$update_id
		);

		$uids = $wpdb->get_col( $sql );
		if(sizeof($uids)==0)
		{
			 echo json_encode(array('status'=>'success','description'=>array('cnt_member'=>0)));
		}
		else
		{
			$user_query = new WP_User_Query( array( 'include' => $uids  ,'orderby' => 'id', 'order' => 'ASC') );
			if ( $user_query->get_results()) {
				$cnt = 0;
				$obj=array();
				foreach ( $user_query->get_results() as $ukey => $user )
				{
					$number 					= get_user_meta($user->ID, 'billing_phone', true);
					$obj[$ukey]['person_name'] 	= $user->display_name;
					$obj[$ukey]['number'] 		= $number;
					$last_sync_id 				= $user->ID;
					$cnt++;
				}
				$resp = SmsAlertcURLOTP::create_contact($obj,$group_name);
				update_option('smsalert_sync',array('last_sync_userId'=>$last_sync_id));//update last_sync_id
				$result = (array)json_decode($resp,true);
				if($result['status']=='success'){
					echo json_encode(array('status'=>'success','description'=>array('cnt_member'=>$cnt)));
				}
			 } else {
				 echo json_encode(array('status'=>'success','description'=>array('cnt_member'=>0)));
			 }
		}
	}

	static function send_smsalert_balance()
	{
		$date = date("Y-m-d");
		$update_dateTime = smsalert_get_option( 'last_updated_lBal_alert','smsalert_background_task','');

		if($update_dateTime == $date)
		{
			return;
		}

		$username 		= smsalert_get_option( 'smsalert_name', 'smsalert_gateway', '');  //smsalert auth username
		$low_bal_val 	= smsalert_get_option( 'low_bal_val', 'smsalert_general', '1000');//from alert box
		$To_mail		= smsalert_get_option( 'alert_email', 'smsalert_general', '');
		$trans_credit 	= self::only_credit();

		if(!empty($trans_credit)){

			foreach($trans_credit as $credit){
				//Email template with content
				$params = array(
					'trans_credit' => $credit,
					'username' => $username,
					'admin_url' => admin_url(),
				);
				$emailcontent = get_smsalert_template('template/emails/smsalert-low-bal.php',$params);

				if($credit <= $low_bal_val)
				{
					wp_mail( $To_mail, '❗ ✱ SMS Alert ✱ Low Balance Alert', $emailcontent,'content-type:text/html');
				}
			}

			update_option('smsalert_background_task',array('last_updated_lBal_alert'=>date('Y-m-d')));//update last time and date
		}
	}

	function daily_email_alert(){
		$username = smsalert_get_option( 'smsalert_name', 'smsalert_gateway', '');  //smsalert auth username
		$date = date("Y-m-d");
		$To_mail=smsalert_get_option( 'alert_email', 'smsalert_general', '');
		$update_dateTime = smsalert_get_option( 'last_updated_dBal_alert','smsalert_background_dBal_task','');

		if($update_dateTime == $date)
		{
			return;
		}

		$daily_credits = self::only_credit();

		if(!empty($daily_credits)){

			foreach($daily_credits as $credit){
				//email content
				$params = array(
						'daily_credits' => $credit,
						'username' => $username,
						'date' => $date,
						'admin_url' => admin_url(),
				);
				$dailyemailcontent = get_smsalert_template('template/emails/daily_email_alert.php',$params);
				update_option('smsalert_background_dBal_task',array('last_updated_dBal_alert'=>date('Y-m-d')));//update last time and date
				wp_mail($To_mail, '✱ SMS Alert ✱ Daily  Balance Alert ',$dailyemailcontent,'content-type:text/html');
			}
		}
	 }
    /**
     * Update Order buyer notify meta in checkout page
     * @param  integer $order_id
     * @return void
     */
    function buyer_notification_update_order_meta( $order_id ) {
        if ( ! empty( $_POST['buyer_sms_notify'] ) ) {
            update_post_meta( $order_id, '_buyer_sms_notify', sanitize_text_field( $_POST['buyer_sms_notify'] ) );
        }
    }

	public function sa_wc_order_place($order_id) {
		if (!$order_id) {
           return;
		}
		WooCommerceCheckOutForm::trigger_after_order_place( $order_id, 'pending', 'pending' );
	}
} // SMSAlert_WC_Order_SMS

/**
 * Loaded after all plugin initialize
 */
add_action( 'plugins_loaded', 'load_SMSAlert_wc_order_sms' );

function additional_cron_intervals($intervals)
{
	$intervals['sendsms_interval'] = array( //Defining cron Interval for sending out msg notifications
		'interval' => CART_CRON_INTERVAL * 60,
		'display' => 'Every 10 minutes'
	);
	return $intervals;
}

add_filter( 'cron_schedules', 'additional_cron_intervals');

function load_SMSAlert_wc_order_sms() {
    $smsalert = SMSAlert_WC_Order_SMS::init();
}
register_activation_hook( __FILE__, 	array('smsalert_WC_Order_SMS', 'run_on_activate'));
register_deactivation_hook( __FILE__, 	array('smsalert_WC_Order_SMS', 'run_on_deactivate'));
register_uninstall_hook(__FILE__, 		array('smsalert_WC_Order_SMS', 'run_on_uninstall'));//delete cart table data
?>