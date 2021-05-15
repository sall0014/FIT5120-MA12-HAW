<?php
echo '<style>.modal{display:none;position:fixed!important;z-index:999999999999;padding-top:100px!important;left:0;top:0;width:100%!important;height:100%!important;overflow:auto;background-color:rgb(0,0,0);background-color:rgba(0,0,0,0.4)!important;}.wpforms-form .smsalertModal .modal-content{width:40%!important} .smsalertModal .modal-content{position:relative;background-color:#fefefe!important;margin:auto!important;padding:0;border:1px solid #888;width:40%;box-shadow:04px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);-webkit-animation-name:zoomIn;-webkit-animation-duration:0.3s;animation-name:zoomIn;animation-duration:0.3s}.modal-body{padding:1px!important}.wpforms-form .woocommerce-error,.wpforms-form .woocommerce-info,.wpforms-form .woocommerce-message {background-color: #f7f6f7!important;padding: 1em 2em 1em 3.5em!important;color: #2c2b2b;}.wpforms-form .woocommerce-error {border-top: 3px solid #b81c23!important;}[name=smsalert_otp_validate_submit]{width:100%!important;}@media  only screen and (max-width: 767px){.wpforms-form .smsalertModal .modal-content{width:100%!important} .smsalertModal .modal-content{width:100%}}@-webkit-keyframes zoomIn {from {opacity: 0;-webkit-transform: scale3d(0.3, 0.3, 0.3);transform: scale3d(0.3, 0.3, 0.3);}50% {opacity: 1;}}@keyframes zoomIn {from {opacity: 0;-webkit-transform: scale3d(0.3, 0.3, 0.3);transform: scale3d(0.3, 0.3, 0.3);}50% {opacity: 1;}}.zoomIn {-webkit-animation-name: zoomIn;animation-name: zoomIn;}.modal-header{background-color:#5cb85c;color:white;}.modal-footer{background-color:#5cb85c;color:white;}.close{float:none;text-align: right;font-size: 25px;cursor: pointer;text-shadow: 0 1px 0 #fff;line-height: 1;font-weight: 400;padding: 0px 5px 0px!important;}.close:hover {color: #999;}.otp_input{margin-bottom:12px;}.otp_input[type="number"]::-webkit-outer-spin-button, .otp_input[type="number"]::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}
.otp_input[type="number"] {-moz-appearance: textfield;width:100%}.otp_input{width:100%}
form.sa_popup {overflow:hidden}
form.sa_popup .modal{padding-top:0;}
form.sa_popup .modal-content{width:100%;height:100%;}

form.sa_popup + .sa-lwo-form .smsalertModal{padding-top:0 !important}
form.sa_popup + .sa-lwo-form .modal-content{width:100% !important}
.wpforms-form .smsalert_otp_validate_submit {
background: #f7f6f7!important;
}
</style>';

$otp_input = (!empty($otp_input_field_nm)) ? $otp_input_field_nm : 'smsalert_customer_validation_otp_token';
echo '<div  class="modal smsalertModal">
   <div class="modal-content">
      <div class="close" >x</div>
      <div class="modal-body">
         <div style="margin:1em;position:relative" class="sa-message">EMPTY</div>
         <div class="smsalert_validate_field" style="margin:1em"><input type="number" name="'.$otp_input.'" autofocus="true" placeholder="" id="'.$otp_input.'" class="input-text otp_input" pattern="[0-9]{4,8}" title="'.SmsAlertMessages::showMessage('OTP_RANGE').'"><a style="pointer-events: none; cursor: default; opacity: 1; float:right" class="sa_resend_btn" onclick="saResendOTP(this)">'.__("Resend",'sms-alert').'</a><span class="sa_timer" style="min-width:80px; float:right">00:00:02 sec</span><br><button type="button" name="smsalert_otp_validate_submit" style="color:grey; pointer-events:none;" class="button smsalert_otp_validate_submit" value="'.SmsAlertMessages::showMessage('VALIDATE_OTP').'">'.SmsAlertMessages::showMessage('VALIDATE_OTP').'</button></div>
      </div>
   </div>
</div>';

echo '<script>

jQuery("form .smsalertModal").on("focus", "input[type=number]", function (e) {
	jQuery(this).on("wheel.disableScroll", function (e) {
		e.preventDefault();
	});
});
jQuery("form .smsalertModal").on("blur", "input[type=number]", function (e) {
jQuery(this).off("wheel.disableScroll");
});
</script>';
?>