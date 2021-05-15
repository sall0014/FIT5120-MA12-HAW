<?php
if (! defined( 'ABSPATH' )) exit;
if(!is_plugin_active('woocommerce/woocommerce.php')){return;}
class sa_backinstock
{
	public function __construct() {
		add_filter('sAlertDefaultSettings',  __CLASS__ .'::addDefaultSetting',1);
		add_action( 'woocommerce_product_set_stock', array($this, 'trigger_on_product_stock_changed'), 10 ,1);
        add_action('woocommerce_variation_set_stock_status', array($this, 'trigger_on_variation_stock_changed'), 999,3);
		$smsalert_bis_subscribed_notify 	= smsalert_get_option( 'subscribed_bis_notify', 'smsalert_bis_general', 'on');

		if($smsalert_bis_subscribed_notify == 'on'){
			add_action('woocommerce_simple_add_to_cart', array($this, 'display_in_simple_product'), 63);
			add_action('woocommerce_after_variations_form', array($this, 'sa_display_in_no_variation_product'));
			add_filter('woocommerce_available_variation', array($this, 'sa_display_in_variation'), 100, 3);
			$this->handleSubcribeRequest($_REQUEST);
		}

		if (is_plugin_active('woocommerce/woocommerce.php' )){
			add_action( 'sa_addTabs', array( $this, 'addTabs' ), 100 );
		}
		add_action( 'wp_enqueue_scripts', array($this,'enqueue_script_on_page'));
		add_action( 'manage_posts_custom_column', array($this,'smsalert_populate_subscriber'),10,2 );
		add_action('woocommerce_product_options_inventory_product_data', array($this,'show_subscriber_in_single_product'));
		
		add_action( 'woocommerce_variation_options_pricing', array($this,'show_subscriber_in_variation_product'), 10, 3 );
	}
	
	
 
	function show_subscriber_in_variation_product( $loop, $variation_data, $variation ) {
		echo '<p class="form-field form-row form-row-full">
			<label for="smsalert_backinstock_subscribers">'.__("Backorders", "sms-alert").'</label>
			<input type="text" name="smsalert_backinstock_subscribers" value="'.All_Subscriber_List::get_nos_subscribers_by_productId($variation->ID).'" class="input-text short smsalert_backinstock_subscribers" disabled style="border:none;box-shadow:none"/>
		</p>';
	}
	
	function show_subscriber_in_single_product()
	{
	  global $post;
	  $product_data           = wc_get_product($post->ID);
	  if($product_data->get_type()!='variable')
	  echo '<p class="form-field">
            <label for="smsalert_backinstock_subscribers">'.__("Backorders", "sms-alert").'</label>
			<input type="text" name="smsalert_backinstock_subscribers" value="'.All_Subscriber_List::get_nos_subscribers_by_productId($post->ID).'" class="input-text short smsalert_backinstock_subscribers" disabled style="border:none;box-shadow:none"/>
        </p>';
	}
	 
	function smsalert_populate_subscriber( $column_name,$product_id ) {
 
		if( $column_name  == 'is_in_stock' ) {
			$backorders = All_Subscriber_List::get_nos_subscribers_for_productlist($product_id);
			if($backorders>0)
			{
				echo " <strong>Backorders</strong> (".$backorders.") <br><br>";
			}
		}
	}
	
	function enqueue_script_on_page(){
		if(!is_product()) return;
		
		wp_register_script( 'sa_single_product', SA_MOV_URL . 'js/wc-product.js' , array('jquery') ,SmsAlertConstants::SA_VERSION,true);
		
		wp_localize_script( 'sa_single_product', 'sa_otp_settings',array(
							'show_countrycode' 			=> smsalert_get_option( 'checkout_show_country_code', 'smsalert_general', 'off'),
		),
		'sa_default_countrycode',smsalert_get_option('default_country_code', 'smsalert_general')
		);
		
		
		wp_localize_script('sa_single_product', 'sa_notices',array(
			'waiting_txt'		=> __("Please wait...","sms-alert"),
			'enter_here'		=> __("Enter Number Here","sms-alert"),
		));
		wp_enqueue_script('sa_single_product');
	}

	/*add tabs to smsalert settings at backend*/
	public static function addTabs($tabs=array())
	{
		$backinstock_param = array(
			'checkTemplateFor'	=> 'backinstock',
			'templates'			=> self::getBackInStockTemplates(),
		);

		$tabs['woocommerce']['inner_nav']['backinstock']['title']		= __("Notify Me",'sms-alert');
		$tabs['woocommerce']['inner_nav']['backinstock']['tab_section']	= 'backinstocktemplates';
		$tabs['woocommerce']['inner_nav']['backinstock']['tabContent']	= self::getContentFromTemplate('views/message-template.php',$backinstock_param);
		$tabs['woocommerce']['inner_nav']['backinstock']['icon']		= 'dashicons-products';
		return $tabs;
	}
	
	public static function getContentFromTemplate($path,$params=array())
	{
		return get_smsalert_template($path,$params);
	}

	public static function getBackInStockTemplates()
	{
		$variables = array(
			'[item_name]' 		=> 'Product Name',
			'[name]' 			=> 'Name',
			'[subscribed_date]' => 'Date',
			'[store_name]' 		=> 'Store Name',
			'[shop_url]' 		=> 'Shop Url',
		);

		//product back in stock
		$current_val 	= smsalert_get_option( 'customer_bis_notify', 'smsalert_bis_general', 'on');
		$checkboxNameId	= 'smsalert_bis_general[customer_bis_notify]';
		$textareaNameId	= 'smsalert_bis_message[customer_bis_notify]';
		$text_body 		= smsalert_get_option('customer_bis_notify', 'smsalert_bis_message',
		SmsAlertMessages::showMessage('DEFAULT_BACK_IN_STOCK_CUST_MSG'));

		$templates 		= array();

		$templates['backinstock_msg']['title'] 			= 'Send message to customer when product is back in stock';
		$templates['backinstock_msg']['enabled'] 		= $current_val;
		$templates['backinstock_msg']['status'] 		= 'backinstock_msg';
		$templates['backinstock_msg']['text-body'] 		= $text_body;
		$templates['backinstock_msg']['checkboxNameId'] = $checkboxNameId;
		$templates['backinstock_msg']['textareaNameId'] = $textareaNameId;
		$templates['backinstock_msg']['token'] 			= $variables;

		//product subscribed
		$current_val 	= smsalert_get_option( 'subscribed_bis_notify', 'smsalert_bis_general', 'on');
		$checkboxNameId	= 'smsalert_bis_general[subscribed_bis_notify]';
		$textareaNameId	= 'smsalert_bis_message[subscribed_bis_notify]';
		$text_body 		= smsalert_get_option('subscribed_bis_notify', 'smsalert_bis_message',
		SmsAlertMessages::showMessage('DEFAULT_BACK_IN_STOCK_SUBSCRIBE_MSG'));

		$templates['subscribed']['title'] 			= 'Send message to customer when product is subscribed';
		$templates['subscribed']['enabled'] 		= $current_val;
		$templates['subscribed']['status'] 			= 'subscribed';
		$templates['subscribed']['text-body'] 		= $text_body;
		$templates['subscribed']['checkboxNameId']	= $checkboxNameId;
		$templates['subscribed']['textareaNameId']	= $textareaNameId;
		$templates['subscribed']['token'] 			= $variables;

		return $templates;
	}

	public function trigger_on_product_stock_changed($product)
	{
		$product_id 	= $product->get_id();
		$product_status = $product->get_stock_status();
		$this->processSmsForInStockSubscribers($product_id,$product_status);
	}

	public function trigger_on_variation_stock_changed($variation_id,$variation_status,$obj)
	{
		$this->processSmsForInStockSubscribers($variation_id,$variation_status);
	}

	public function processSmsForInStockSubscribers($product_id,$product_status){
		global $wpdb;
		$table_prefix 	= $wpdb->prefix;
		$datas 			= $wpdb->get_results( "SELECT * FROM {$table_prefix}postmeta WHERE meta_key = 'smsalert_instock_pid' and meta_value = '$product_id'", ARRAY_A );
		$obj			= array();
		foreach($datas as $dkey => $data){

			$post_id 	= $data['post_id'];
			$post_data 	= $wpdb->get_results( "SELECT post_title, post_author FROM {$table_prefix}posts WHERE post_status = 'smsalert_subscribed' and ID = '$post_id'", ARRAY_A );

			$post_user_id 				= $post_data[0]['post_author'];
			$smsalert_bis_cust_notify 	= smsalert_get_option( 'customer_bis_notify', 'smsalert_bis_general', 'on');
			if(!empty($post_data) && $product_status == 'instock' && $smsalert_bis_cust_notify == 'on'){
				$backinstock_message 	= smsalert_get_option( 'customer_bis_notify', 'smsalert_bis_message', '' );
				$obj[$dkey]['number']	= $post_data[0]['post_title'];
				$obj[$dkey]['sms_body'] = $this->parse_body($post_user_id, $product_id, $backinstock_message);
				$msg_status 			= $this->msg_sent_status($post_id);
			}
		}
		SmsAlertcURLOTP::send_sms_xml( $obj );
	}

	public function handleSubcribeRequest($data)
	{
		if(!empty($data))
		{
			if(isset($data['action']) && $data['action']=='smsalertbackinstock'){
				echo $this->perform_action_on_ajax_data($data);
				exit();
			}
		}
	}

	/*add default settings to savesetting in setting-options*/
	public static function addDefaultSetting($defaults=array())
	{
		$defaults['smsalert_bis_general']['customer_bis_notify']	= 'off';
		$defaults['smsalert_bis_message']['customer_bis_notify']	= '';
		$defaults['smsalert_bis_general']['subscribed_bis_notify']	= 'off';
		$defaults['smsalert_bis_message']['subscribed_bis_notify']	= '';
		return $defaults;
	}

	public function display_in_simple_product() {
		global $product;
		echo $this->display_sa_subscribe_box($product);
	}

	public function sa_display_in_no_variation_product() {
		global $product;
		$product_type = $product->get_type();
		//Get Available variations?
		if ($product_type == 'variable') {

			$get_variations = count($product->get_children()) <= apply_filters('woocommerce_ajax_variation_threshold', 30, $product);
			$get_variations = $get_variations ? $product->get_available_variations() : false;

			if (!$get_variations) {
				echo $this->display_sa_subscribe_box($product);
			}
		}
	}

	public function display_sa_subscribe_box($product, $variation = array())
	{		
		SmsAlertUtility::enqueue_script_for_intellinput();

		$get_option 				= get_option('smsalert_instocksettings');
		$check_guest_visibility 	= isset($get_option['hide_form_guests']) && $get_option['hide_form_guests'] != '' && !is_user_logged_in() ? false : true;
		$check_member_visibility 	= isset($get_option['hide_form_members']) && $get_option['hide_form_members'] != '' && is_user_logged_in() ? false : true;
		$product_id 				= $product->get_id();

		if ($variation) {
			$variation_id = $variation->get_id();
		} else {
			$variation_id = 0;
		}

		$product_status   = $product->get_stock_status();
		if ($check_guest_visibility && $check_member_visibility && $product_status != 'instock' && !$variation_id) {
			$params=array(
				'product_id'	=> $product_id,
				'variation_id'	=> $variation_id,
			);
			return get_smsalert_template('template/backinstock-template.php',$params);
		}
		elseif ($variation && !$variation->is_in_stock() || (($variation && (($variation->managing_stock() && $variation->backorders_allowed() && $variation->is_on_backorder(1)) || $variation->is_on_backorder(1)) && $visibility_backorder)))
		{
			$params=array(
				'product_id'	=> $product_id,
				'variation_id'	=> $variation_id,
			);
			return get_smsalert_template('template/backinstock-template.php',$params);
		}
		else {
			return '';
		}
	}

	public function sa_display_in_variation($atts, $product, $variation) {
		$get_stock 					= $atts['availability_html'];
		$atts['availability_html'] 	= $get_stock . $this->display_sa_subscribe_box($product, $variation);
		return $atts;
	}

	public function perform_action_on_ajax_data($post_data) {
		global $phoneLogic;
		if(is_user_logged_in()){
			$user_id = get_current_user_id();
		}
		else{
			$user_id = 0;
		}

		$user_phone 		= isset($post_data['user_phone'])? $post_data['user_phone'] : '';
		$product_id 		= isset($post_data['product_id'])? $post_data['product_id'] : '';
		$variation_id 		= isset($post_data['variation_id'])? $post_data['variation_id'] : '';

		$subscriber_phone 	= SmsAlertcURLOTP::checkPhoneNos($user_phone);
		if(!$subscriber_phone)
		{
			$data['status'] 	 = 'error';
			$data['description'] =  str_replace("##phone##",$user_phone,$phoneLogic->_get_otp_invalid_format_message());
		}
		else
		{
			$get_user_id 		= $user_id;
			$product_id 		= $product_id;
			$variation_id 		= $variation_id;

			$check_is_already_subscribed = $this->is_already_subscribed($product_id, $variation_id, $subscriber_phone, $get_user_id);

			$data=array();

			if(!empty($check_is_already_subscribed))
			{
				$data['status'] 	 = 'error';
				$data['description'] =  __('Seems like you have already subscribed to this product','sms-alert');
			}
			else
			{
				if($subscriber_phone != ''){

					$post_id = $this->insert_subscriber($subscriber_phone,$get_user_id);

					$default_data = array(
						'smsalert_instock_variation_id' => $variation_id,
						'smsalert_subscriber_phone' 	=> $subscriber_phone,
						'smsalert_instock_user_id' 		=> $get_user_id,
						'smsalert_instock_pid' 			=> $variation_id > '0' || $variation_id > 0 ? $variation_id : $product_id,
					);
					foreach ($default_data as $key => $value) {
						update_post_meta($post_id, $key, $value);
					}
					$smsalert_bis_cust_notify 	= smsalert_get_option( 'customer_bis_notify', 'smsalert_bis_general', '');
					$subscribed_message 		= smsalert_get_option( 'subscribed_bis_notify', 'smsalert_bis_message', '' );

					if($smsalert_bis_cust_notify=='on' && $subscribed_message!='')
					{
						$buyer_sms_data['number'] 		= $subscriber_phone;
						$buyer_sms_data['sms_body']   	= $this->parse_body($get_user_id, $product_id, $subscribed_message);
						SmsAlertcURLOTP::sendsms( $buyer_sms_data );
					}
					$data['status'] 	 = 'success';
					$data['description'] =  __('You have subscribed successfully.','sms-alert');
				}
			}
		}
		return json_encode($data);
	}

	public function insert_subscriber($mobileno,$user_id) {
		$args = array(
			'post_title' 	=> $mobileno,
			'post_type' 	=> 'sainstocknotifier',
			'post_status' 	=> 'smsalert_subscribed',
			'post_author'   => $user_id,
		);
		global $wp_rewrite;
		$wp_rewrite = new wp_rewrite;
		$id = wp_insert_post($args);
		if (!is_wp_error($id)) {
			return $id;
		} else {
			return false;
		}
	}

	public function parse_body($post_user_id=NULL, $product_id, $message){
		$item_name 	= get_the_title($product_id);
		$user_data 	= get_userdata($post_user_id, ARRAY_A);
		$find = array(
            '[item_name]',
            '[name]',
            '[store_name]',
            '[shop_url]',
        );

		$replace = array(
			wp_specialchars_decode($item_name),
			(isset($user_data->user_login) ? $user_data->user_login : ''),
			get_bloginfo(),
			get_permalink($product_id),
		);

        $message 	= str_replace( $find, $replace, $message);
		return $message;
	}

	public function is_already_subscribed($product_id, $variation_id, $subscriber_phone, $get_user_id) {

		global $wpdb;
		
		$wcc_ph 		= SmsAlertcURLOTP::checkPhoneNos($subscriber_phone);
		$wocc_ph    	= SmsAlertcURLOTP::checkPhoneNos($subscriber_phone,false);
		$wth_pls_ph    	= '+'.$wcc_ph;
		
		
		$table_prefix 	= $wpdb->prefix;
		$product_id 	= ($variation_id > '0' || $variation_id > 0) ? $variation_id : $product_id;
		$datas 			= $wpdb->get_results( "SELECT * FROM {$table_prefix}postmeta pm1 inner join {$table_prefix}postmeta pm2 on pm1.post_id= pm2.post_id WHERE pm1.meta_key = 'smsalert_instock_pid' and pm1.meta_value = '$product_id' and pm2.meta_key ='smsalert_subscriber_phone' and pm2.meta_value in('$wcc_ph','$wocc_ph','$wth_pls_ph')", ARRAY_A );
		
		$post_ids 	= array_map(function($item){return $item['post_id'];},$datas);
		$post_data 	= array();
		
		if(!empty($post_ids))
		{
			$post_data = $wpdb->get_results( "SELECT ID,post_title, post_status FROM {$table_prefix}posts WHERE post_status = 'smsalert_subscribed' and ID in (".implode(',',$post_ids).")", ARRAY_A );
		}
		return $post_data;
	}

	public function msg_sent_status($subscribe_id) {
        $args = array(
            'ID' 			=> $subscribe_id,
            'post_type' 	=> 'sainstocknotifier',
            'post_status' 	=> 'smsalert_msgsent',
        );
        $id = wp_update_post($args);
        return $id;
    }
}
new sa_backinstock;
?>
<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class All_Subscriber_List extends WP_List_Table {

	function __construct()
	{
		parent::__construct(array(
			'singular' => 'backinstock',
			'plural' => 'backinstocks',
		));
	}

	/*get all subscriber info*/
	public static function get_all_subscriber( $per_page = 5, $page_number = 1 ) {

	  global $wpdb;

	  $sql = "SELECT P.ID, P.post_author, P.post_title, P.post_status, PM.meta_value FROM {$wpdb->prefix}posts P inner join {$wpdb->prefix}postmeta PM on P.ID = PM.post_id WHERE P.post_type = 'sainstocknotifier' and PM.meta_key = 'smsalert_instock_pid'";

	  if ( ! empty( $_REQUEST['orderby'] ) ) {
		$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
		$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' DESC';
	  }
	  else
	  {
		$sql .= ' ORDER BY post_date desc';
	  }

	  $sql .= " LIMIT $per_page";

	  $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

	  $result = $wpdb->get_results( $sql, 'ARRAY_A' );

	  return $result;
	}
	
	
	
	public static function get_nos_subscribers_by_productId($product_id=null) {
		global $wpdb;
		$sql = "SELECT count(*) as cnt FROM {$wpdb->prefix}posts P inner join {$wpdb->prefix}postmeta PM on P.ID = PM.post_id WHERE P.post_type = 'sainstocknotifier' and PM.meta_key = 'smsalert_instock_pid' and P.post_status = 'smsalert_subscribed'";
		$sql.="and PM.meta_value='".$product_id."'"; 
		$result = $wpdb->get_results( $sql, 'ARRAY_A');
		return (!empty($result)) ? $result[0]['cnt'] : 0;
	}
	
	public static function get_nos_subscribers_for_productlist($product_id=null) {
		global $wpdb;
		global $product;
		$sql = "SELECT count(*) as cnt FROM {$wpdb->prefix}posts P inner join {$wpdb->prefix}postmeta PM on P.ID = PM.post_id WHERE P.post_type = 'sainstocknotifier' and PM.meta_key = 'smsalert_instock_pid' and P.post_status = 'smsalert_subscribed'";
		$product_type = $product->get_type();
		if($product_type=='variable')
		{
		  $product_ids = count($product->get_children())>0 ? $product->get_children() : array();
		  $sql.="and PM.meta_value in (".implode(',',$product_ids).")";
		}
		else
		{
		  $sql.="and PM.meta_value='".$product_id."'"; 
		}
		$result = $wpdb->get_results( $sql, 'ARRAY_A');
		return (!empty($result)) ? $result[0]['cnt'] : 0;
	}
	


	/** Text displayed when no data is available */
	public function no_items() {
	  _e( 'No Subscriber.', 'sp' );
	}

	/**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */

	function column_default($item, $column_name)
	{
		return $item[$column_name];
	}

	/**
	 * checkbox shown in every row especially for bulk action.
	 */
	function column_cb($item)
	{
		return sprintf(
			'<input type="checkbox" name="ID[]" value="%s" />',
			$item['ID']
		);
	}

	function column_post_status($item)
	{
		if($item['post_status'] == 'smsalert_subscribed'){
			$post_status = '<button class="button-primary"/>Subscribed</a>';
		}else{
			$post_status = '<button class="button-primary" style="background: green;border: 1px solid green;">Message Sent</a>';
		}

		return $post_status;
	}

	function column_post_author($item)
	{
		if($item['post_author'] == '0'){
			$register_or_not = '<button class="button-primary" style="background: red;border: 1px solid red;">Guest</a>';
		}else{
			$register_or_not = '<button class="button-primary" style="background: green;border: 1px solid green;">Yes</a>';
		}

		return $register_or_not;
	}

	function column_meta_value($item)
	{
		$product_name = '<a href="'.get_permalink($item['meta_value']).'" target="_blank">'.get_the_title($item['meta_value']).'</a>';

		return $product_name;
	}

	/**
	 *Get columns shown in table.
	 */
	function get_columns() {
	  $columns = [
		'cb'      		=> '<input type="checkbox" />',
		'post_title' 	=> __( 'Mobile Number'),
		'post_status'   => __( 'Status'),
		'meta_value'    => __( 'Product'),
		'post_author'   => __( 'Registered User' ),
	  ];

	  return $columns;
	}


	 /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'post_author' => array('post_author', true),
            'post_title' => array('post_title', false),
            'post_status' => array('post_status', false),
			'meta_value' => array('meta_value', false),
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
    function process_bulk_action()
    {
        global $wpdb;
        //$table_name = $wpdb->prefix; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['ID']) ? $_REQUEST['ID'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE P, PM FROM {$wpdb->prefix}posts P inner join {$wpdb->prefix}postmeta PM on P.ID = PM.post_id WHERE ID IN($ids)");
            }
        }
    }

	/*get total records of the table.*/
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}posts where post_type = 'sainstocknotifier'";

		return $wpdb->get_var( $sql );
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$per_page 		= 10;
		$current_page 	=(isset($_REQUEST['paged'])?$_REQUEST['paged']:1);
		$columns 		= $this->get_columns();
		$this->items 	= self::get_all_subscriber( $per_page, $current_page );


		// [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$total_items = self::record_count();
		// [REQUIRED] configure pagination
		$this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' 	=> $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
		));

		// here we configure table headers, defined in our methods
		$this->_column_headers = array($columns, $hidden, $sortable);

		return $this->items;
	}
}

function all_subscriber_admin_menu()
{
	add_submenu_page( null, 'All Subscriber','All Subscriber', 'manage_options', 'all-subscriber', 'subscriber_page_handler');
}

add_action('admin_menu', 'all_subscriber_admin_menu');

/**
 * List page handler
 *
 * This function renders our custom table
 * Notice how we display message about successfull deletion
 * Actualy this is very easy, and you can add as many features
 * as you want.
 *
 * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
 */
function subscriber_page_handler()
{
    global $wpdb;

    $table_data = new All_Subscriber_List();
	$data = $table_data->prepare_items();
	$message = '';
    if ('delete' === $table_data->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'custom_table_example'), count($_REQUEST['ID'])) . '</p></div>';
    }
?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2>All Subscriber</h2>
    <?php echo $message; ?>

    <form id="persons-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table_data->display() ?>
    </form>
</div>
<?php } ?>