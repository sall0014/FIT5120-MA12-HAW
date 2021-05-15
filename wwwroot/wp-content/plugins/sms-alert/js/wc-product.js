jQuery(document).on("click", "#sa_bis_submit", function () {
	var self 		 = this;
	var waiting_txt  = (typeof sa_notices !=  'undefined' && sa_notices['waiting_txt']) ? sa_notices['waiting_txt'] : "Please wait...";
	jQuery(self).val(waiting_txt).attr( "disabled", "disabled" );
	
	var phone_number = jQuery("[name=billing_phone]").val()?jQuery("[name=billing_phone]").val():jQuery("#sa_bis_phone").val();
	
	if(sa_otp_settings['show_countrycode']=='off'){
		jQuery(".sa_phone_error").remove();
		jQuery(".phone-valid").after("<span class='error sa_phone_error' style='display:none'></span>");
	}
	
	if(phone_number == ''){
		jQuery(".sa_phone_error").html("Please fill the number").fadeIn().css({"color":"red"});
		jQuery("#sa_bis_submit").val("Notify Me").removeAttr("disabled",false);
		return false;
	}
	
	if(jQuery(self).is("input")){
		jQuery(self).val(waiting_txt).attr("disabled",true);
	}else{
		jQuery(self).text(waiting_txt).attr("disabled",true);
	}
	
	var product_id 	 = jQuery("#sa-product-id").val();
	var var_id 		 = jQuery("#sa-variation-id").val();
	var data = {
		product_id: product_id,
		variation_id: var_id,
		user_phone: phone_number,
		action: "smsalertbackinstock"
	};
	jQuery.ajax({
		type: "post",
		data: data,
		success: function (msg) {
			var r= jQuery.parseJSON(msg);
			jQuery("fieldset").hide();
			if(r.status == "success"){
				jQuery(".sastock_output").html(r.description).fadeIn().css({"color":"#fff", 'background-color':'green'});
			}else{
				jQuery(".sastock_output").html(r.description).fadeIn().css({"color":"#fff",'background-color':'red'});
			}
			jQuery(".sastock_output").css({'padding':'10px','border-radius':'4px','margin-bottom':'10px'});
		},
		error: function (request, status, error) {	}
	});							
	return false;
});
jQuery(".single_variation_wrap").on("show_variation", function (event, variation) {
	jQuery(".phone-valid").after("<span class='error sa_phone_error' style='display:none'></span>");
	// Fired when the user selects all the required dropdowns / attributes
	// and a final variation is selected / shown
	var vid = variation.variation_id;
	jQuery(".smsalert_instock-subscribe-form").hide(); //remove existing form
	jQuery(".smsalert_instock-subscribe-form-" + vid).fadeIn(1000,'linear',function(){
	
	if(sa_otp_settings['show_countrycode']=='on')
	{
		var default_cc = (typeof sa_default_countrycode !='undefined' && sa_default_countrycode!='') ? sa_default_countrycode : '91';
		jQuery(this).find('.phone-valid').intlTelInput("destroy");
		
		var default_opt = {
			separateDialCode: true,
			nationalMode: true,
			formatOnDisplay: false,
			hiddenInput: "billing_phone",
			utilsScript: "/utils.js?v=3.3.1"
		};
		
		if(default_cc!='')
		{
			var object = jQuery.extend({},default_opt);
		}
		else
		{
			
			var object = jQuery.extend(default_opt, {initialCountry: "auto",geoIpLookup: function(success, failure) {
					jQuery.get("https://ipapi.co/json/").always(function(resp) {
						var countryCode = (resp && resp.country) ? resp.country : "US";
						success(countryCode);
						
					}).fail(function() {
						console.log("ip lookup is not working.");
					});
			}});
		}
		
		var iti = jQuery(this).find(".phone-valid").intlTelInput(object);
		if(default_cc!='')
		{
			var selected_cc = getCountryByCode(default_cc);
			var show_default_cc = selected_cc[0].iso2.toUpperCase();
			iti.intlTelInput("setCountry",show_default_cc);
		}
		
	}
	}); //add subscribe form to show
});

//get all country data		
function getCountryByCode(code) {
	return window.intlTelInputGlobals.getCountryData().filter(
	function(data){ return (data.dialCode == code) ? data.iso2 : ''; }
	);
}