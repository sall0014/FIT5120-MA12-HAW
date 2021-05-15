<?php
if (! defined( 'ABSPATH' )) exit;
class SA_Backend
{
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'smsalert_review' ), 10 );
		$this->routeData();
	}

	function routeData(){
		if(!array_key_exists('option', $_GET)) return;
		switch (trim($_GET['option']))
		{
			case "not-show-again":
				add_option('smsalert_review_not_show_again',0);
				break;
			case "remind-later":
			    $smsalert_admin_notice_user_meta = array(
	                'date-dismissed' => date( 'Y-m-d' ),
	            );
			    update_user_meta( get_current_user_id(), 'smsalert_review_remind_later', $smsalert_admin_notice_user_meta);
				break;
		}
	}

	function smsalert_review(){
		$current_date 	= date('Y-m-d');
		$date 			= get_option('smsalert_activation_date',date('Y-m-d'));
		$show_date 		= date("Y-m-d", strtotime("+1 month", strtotime($date)));
		$show 			= get_option('smsalert_review_not_show_again',1);
		$user_meta 		= get_user_meta( get_current_user_id(), 'smsalert_review_remind_later' );
		$remind=0;
        if ( isset( $user_meta[0]['date-dismissed'] ) ) {
			$date_1 	= $user_meta[0]['date-dismissed'];
	        $date_2 	= date("Y-m-d", strtotime("+7 days", strtotime($date_1)));;
	        if ( $current_date > $date_2 ) {
                $remind = 0;
	        }
			else{
				$remind = 1;
			}
		}
		if($show=='1' && $remind=='0' && $current_date > $show_date){
			$current_user = wp_get_current_user();
?>
            <div class="notice notice-info">
	            <p>
					<?php
						$username = $current_user->user_firstname ? $current_user->user_firstname : $current_user->nickname
					?>
					<span><?php echo sprintf( __( 'Hi %s ! You\'ve been using the <b>SMS Alert Order Notifications Plugin</b> for a while now. If you like the plugin please support our development by leaving a ★★★★★ rating : <a href="%s" target="_blank">Rate it!</a>', 'smsalert' ),$username , 'https://wordpress.org/support/view/plugin-reviews/sms-alert?rate=5#postform'); ?></span>
					<span>
						<a href="javascript:void(0)" class="smsalert-review sa-delete-btn alignright" option="remind-later"><span class="dashicons dashicons-dismiss"></span><?php _e('Dismiss', 'smsalert')?></a>
					</span>
	            </p>
	            <p>
					<span>
						<?php echo sprintf( __('Or else, please leave us a support question in the forum. We\'ll be happy to assist you: <a href="%s">Get support</a> &nbsp;&nbsp; <a href="javascript:void(0)" class="smsalert-review" option="not-show-again">Don\'t show again</a>', 'smsalert' ), 'https://wordpress.org/support/plugin/sms-alert'); ?>
					</span>
	            </p>
	        </div>
            <?php
			echo '
			<script>
				jQuery(".smsalert-review").unbind("click").bind("click", function() {
					var type = jQuery(this).attr("option");
					var action_url = "'.site_url().'/?option="+type;
					jQuery.ajax({
						url:action_url,
						type:"GET",
						crossDomain:!0,
						success:function(o){
							location.reload();
						}
					});
				});
			</script>';
		}
	}
}
new SA_Backend;
?>