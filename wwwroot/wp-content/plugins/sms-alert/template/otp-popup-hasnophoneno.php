<?php
echo'	<html>
					<head>
						<meta http-equiv="X-UA-Compatible" content="IE=edge">
						<meta name="viewport" content="width=device-width, initial-scale=1">
						<link rel="stylesheet" type="text/css" href="' . $css_url . '" />
						<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
					</head>
					<body>
						<div class="sa-modal-backdrop">
							<div class="sa_customer_validation-modal" tabindex="-1" role="dialog" id="mo_site_otp_choice_form">
								<div class="sa_customer_validation-modal-backdrop"></div>
								<div class="sa_customer_validation-modal-dialog sa_customer_validation-modal-md">
									<div class="login sa_customer_validation-modal-content">
										<div class="sa_customer_validation-modal-header">
											<b>'.__("Validate your Phone Number",'sms-alert').'</b>
											<a class="go_back" href="#" onclick="window.location =\''.$goBackURL.'\'" > &larr;
												'.__("Go Back",'sms-alert').'</a>
										</div>
										<div class="sa_customer_validation-modal-body center">
											<div id="message">'.__($message,'sms-alert').'</div><br /> ';
											if(!SmsAlertUtility::isBlank($user_email))
											{
		echo'									<div class="mo_customer_validation-login-container">
													<form name="f" id="validate_otp_form" method="post" action="">
														<input id="validate_phone" type="hidden" name="option" value="smsalert_ajax_form_validate" />
														<input type="hidden" name="form" value="'.$form.'" />
														<input type="text" name="mo_phone_number"  autofocus="true" placeholder="" 
															id="mo_phone_number" required="true" class="sa_customer_validation-textbox" 
															autofocus="true" pattern="^[\+]\d{1,4}\d{7,12}$|^[\+]\d{1,4}[\s]\d{7,12}$" 
															title="'.__("Enter a number in the following format",'sms-alert').': 9xxxxxxxxx"/>
														<div id="salert_message" hidden="" 
															style="background-color: #f7f6f7;padding: 1em 2em 1em 1.5em;color:black;"></div><br/>
														<div id="mo_validate_otp" hidden>
															'.__("Verify Code ",'sms-alert').' <input type="number" 
															name="smsalert_customer_validation_otp_token"  autofocus="true" placeholder="" 
															id="smsalert_customer_validation_otp_token" required="true" 
															class="sa_customer_validation-textbox" autofocus="true" pattern="[0-9]{4,8}" 
															title="'.SmsAlertMessages::showMessage('OTP_RANGE').'"/>
														</div>
														<input type="button" hidden id="validate_otp" name="otp_token_submit" 
															class="smsalert_otp_token_submit"  value="Validate" />
														<input type="button" id="send_otp" class="smsalert_otp_token_submit" 
															value="'.SmsAlertMessages::showMessage('SEND_OTP').'" />';
														sa_extra_post_data($usermeta);
		echo'										</form>
												</div>';
											}
		echo'							</div>
									</div>
								</div>
							</div>
						</div>
						<style> .sa_customer_validation-modal{ display: block !important; } </style>
						<script>
							jQuery(document).ready(function() {
							    $sa = jQuery;
							    $sa("#send_otp").click(function(o) {
							        var e = $sa("input[name=mo_phone_number]").val();
							        $sa("#salert_message").empty(), $sa("#salert_message").append("'.$img.'"), $sa("#salert_message").show(), $sa.ajax({
							            url: "'.site_url().'/?option=smsalert-ajax-otp-generate",
							            type: "POST",
							            data: {billing_phone:e},
							            crossDomain: !0,
							            dataType: "json",
							            success: function(o) {
							                if (o.result == "success") {
							                    $sa("#salert_message").empty(), $sa("#salert_message").append(o.message), 
							                    $sa("#salert_message").css("background-color", "#8eed8e"), 
							                    $sa("#validate_otp").show(), $sa("#send_otp").val("Resend OTP"), 
							                    $sa("#mo_validate_otp").show(), $sa("input[name=mo_validate_otp]").focus()
							                } else {
							                    $sa("#salert_message").empty(), $sa("#salert_message").append(o.message), 
							                    $sa("#salert_message").css("background-color", "#eda58e"), 
							                    $sa("input[name=mo_phone_number]").focus()
							                };
							            },
							            error: function(o, e, n) {}
							        })
							    });
								$sa("#validate_otp").click(function(o) {
							        var e = $sa("input[name=smsalert_customer_validation_otp_token]").val();
							        var f = $sa("input[name=mo_phone_number]").val();
							        var r = $sa("input[name=redirect_to]").val();
							        $sa("#salert_message").empty(), $sa("#salert_message").append("'.$img.'"), $sa("#salert_message").show(), $sa.ajax({
							            url: "'.site_url().'/?option=smsalert-ajax-otp-validate",
							            type: "POST",
							            data: {smsalert_customer_validation_otp_token: e,billing_phone:f,redirect_to:r},
							            crossDomain: !0,
							            dataType: "json",
							            success: function(o) {
							                if (o.result == "success") {
							                    $sa("#salert_message").empty(), $sa("#salert_message").append(o.message), $sa("#validate_phone").remove(), $sa("#validate_otp_form").submit()
							                } else {
							                    $sa("#salert_message").empty(), $sa("#salert_message").append(o.message), 
							                    $sa("#salert_message").css("background-color", "#eda58e"), 
							                    $sa("input[name=validate_otp]").focus()
							                };
							            },
							            error: function(o, e, n) {}
							        })
							    });
							});
						</script>
					</body>
			    </html>';
?>