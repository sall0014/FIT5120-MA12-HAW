<?php
$current_user_id = get_current_user_id();
$phone = (get_user_meta($current_user_id,'billing_phone',true) != '')?SmsAlertUtility::formatNumberForCountryCode(get_user_meta($current_user_id,'billing_phone',true)) : '';
?>
<section class="smsalert_instock-subscribe-form smsalert_instock-subscribe-form-<?php echo $variation_id;?>">
	<div class="panel panel-primary smsalert_instock-panel-primary">
		<form class="panel-body">
			<div class="row">
				<fieldset style="min-inline-size: -webkit-fill-available;min-inline-size: -moz-available;">
				<div class="col-md-12 hide-success">
					<div class="panel-heading smsalert_instock-panel-heading">
						<h4 style="color:currentColor">
							<?php _e( 'Notify Me when back in stock', 'sms-alert' ) ?>
						</h4>
					</div>
					<div class="form-row">
						<input type="text" class="input-text phone-valid" id="sa_bis_phone" name="sa_bis_phone_phone" placeholder="<?php _e( 'Enter Number Here', 'sms-alert' ); ?>" value="<?php echo $phone ;?>"/>
					</div>
					<input type="hidden" id="sa-product-id" name="sa-product-id" value="<?php echo $product_id; ?>"/>
					<input type="hidden" id="sa-variation-id" name="sa-variation-id" value="<?php echo $variation_id; ?>"/>

					<div class="form-group center-block" style="text-align:center;margin-top:10px">
						<?php /*<input type="submit" id="sa_bis_submit" name="smsalert_submit" class="button sa_bis_submit" value="<?php _e( 'Notify Me', 'sms-alert' ) ?>" style="width:100%"/>*/ ?>
						
						
						<button type="submit" id="sa_bis_submit" name="smsalert_submit" class="button sa_bis_submit" value="<?php _e( 'Notify Me', 'sms-alert' ) ?>" style="width:100%"><?php _e( 'Notify Me', 'sms-alert' ) ?></button>
						
					</div>
				</div>
				</fieldset>
				<div class="col-md-12">
					<div class="sastock_output"></div>
				</div>
			</div>
			<!-- End ROW -->
		</form>
	</div>
</section>
<div style="clear:both;"></div>