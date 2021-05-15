	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="username"><?php _e("Mobile Number","sms-alert");?><span class="required">*</span></label>
		<input type="tel" class="woocommerce-Input woocommerce-Input--text input-text sa_mobileno phone-valid" name="username"  value="">
		<span class="error sa_phone_error"></span>
		<input type="hidden" class="woocommerce-Input woocommerce-Input--text input-text" name="redirect"  value="<?php echo $_SERVER['REQUEST_URI'];?>">
	</p>

	<p class="form-row">
		<?php /*<input type="button" class="button smsalert_login_with_otp sa-otp-btn-init" name="smsalert_login_with_otp" value="<?php _e("Login with OTP","sms-alert");?>" >*/ ?>
		<button type="button" class="button smsalert_login_with_otp sa-otp-btn-init" name="smsalert_login_with_otp" value="<?php _e("Login with OTP","sms-alert");?>" ><?php _e("Login with OTP","sms-alert");?></button>
		
		<a href="javascript:void(0)" class="sa_default_login_form"><?php _e("Back","sms-alert");?></a>
	</p>