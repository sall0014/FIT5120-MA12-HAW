<?php
$ninja_forms = SmsAlertNinjaForms::get_ninja_forms(); 
if(!empty($ninja_forms)){ 
?>
<!-- accordion -->	
<div class="cvt-accordion">
	<div class="accordion-section">
	<?php foreach($ninja_forms as $ks => $vs) { ?>		
		<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_cust_<?php echo $ks; ?>">
			<input type="checkbox" name="smsalert_ninja_general[ninja_order_status_<?php echo $ks; ?>]" id="smsalert_ninja_general[ninja_order_status_<?php echo $ks; ?>]" class="notify_box" <?php echo ((smsalert_get_option( 'ninja_order_status_'.$ks, 'smsalert_ninja_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e( ucwords(str_replace('-', ' ', $vs )), 'sms-alert' ) ?></label>
			<span class="expand_btn"></span>
		</a>		 
		<div id="accordion_cust_<?php echo $ks; ?>" class="cvt-accordion-body-content">
			<table class="form-table">
			    <tr>
					<td><input data-parent_id="smsalert_ninja_general[ninja_order_status_<?php echo $ks; ?>]" type="checkbox" name="smsalert_ninja_general[ninja_message_<?php echo $ks; ?>]" id="smsalert_ninja_general[ninja_message_<?php echo $ks; ?>]" class="notify_box" <?php echo ((smsalert_get_option( 'ninja_message_'.$ks, 'smsalert_ninja_general', 'on')=='on')?"checked='checked'":''); ?>/><label>Enable Message</label>
					</td>
				</tr>
				<tr valign="top">
					<td>
						<div class="smsalert_tokens"><?php echo SmsAlertNinjaForms::getNinjavariables($ks); ?></div>
						<textarea data-parent_id="smsalert_ninja_general[ninja_message_<?php echo $ks; ?>]" name="smsalert_ninja_message[ninja_sms_body_<?php echo $ks; ?>]" id="smsalert_ninja_message[ninja_sms_body_<?php echo $ks; ?>]" <?php echo((smsalert_get_option( 'ninja_order_status_'.$ks, 'smsalert_ninja_general', 'on')=='on')?'' : "readonly='readonly'"); ?>><?php echo smsalert_get_option('ninja_sms_body_'.$ks, 'smsalert_ninja_message', SmsAlertMessages::showMessage('DEFAULT_NINJA_CUSTOMER_MESSAGE')); ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						Select Phone Field : <select name="smsalert_ninja_general[ninja_sms_phone_<?php echo $ks; ?>]">
						<?php
						$fields = SmsAlertNinjaForms::getNinjavariables($ks,true);
						?>
						<option value="">--select field--</option>
						<?php
						foreach($fields as $field)
						{
						   if(!is_array($field))
						   {
							?>
							<option value="<?php echo $field; ?>" <?php echo (trim(smsalert_get_option( 'ninja_sms_phone_'.$ks, 'smsalert_ninja_general', '')) == $field) ? 'selected="selected"' : ''; ?> ><?php echo $field; ?></option>
							<?php
						   }
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td><input data-parent_id="smsalert_ninja_general[ninja_order_status_<?php echo $ks; ?>]" type="checkbox" name="smsalert_ninja_general[ninja_otp_<?php echo $ks; ?>]" id="smsalert_ninja_general[ninja_otp_<?php echo $ks; ?>]" class="notify_box" <?php echo ((smsalert_get_option( 'ninja_otp_'.$ks, 'smsalert_ninja_general', 'off')=='on')?"checked='checked'":''); ?>/><label>Enable Mobile Verification</label>
					</td>
				</tr>
			</table>
		</div>
	<?php } ?>	
	</div>
</div>
<!--end accordion-->
<?php 
}else{
	echo "<h3>No Form publish</h3>";
}
?>		