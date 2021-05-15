jQuery( window ).on("load",function() {
    var $ = jQuery;
    var country= $("#billing_country").val();

    //var input = document.querySelector("#billing_phone, .phone-valid");
    var errorMap = ["Invalid number", "Invalid country code", "Please provide a valid Number", "Please provide a valid Number", "Invalid number"];
    $("#billing_phone").after("<p class='error sa_phone_error'></p>");
	$(document).find(".phone-valid").after("<span class='error sa_phone_error' style='display:none'></span>");

	var vars = {};
	var default_cc = (typeof sa_default_countrycode !='undefined' && sa_default_countrycode!='') ? sa_default_countrycode : '91';
	var enter_here = (typeof sa_notices !=  'undefined' && sa_notices['enter_here']) ? sa_notices['enter_here'] : "Enter Number Here";
	
	jQuery("#billing_phone, .phone-valid").each(function(i,item){
		jQuery(item).attr('data-id','sa_intellinput_'+i)
			.attr("placeholder", enter_here)
			.intlTelInput("destroy");
		
		var default_opt = {
			initialCountry: country,
			separateDialCode: true,
			nationalMode: true,
			formatOnDisplay: false,
			hiddenInput: "billing_phone",
			utilsScript: "/utils.js?v=3.3.1"
		};
		
		if(default_cc!='')
		{
			var object = $.extend({},default_opt);
		}
		else
		{
			var object = $.extend(default_opt, {initialCountry: "auto",geoIpLookup: function(success, failure) {
				$.get("https://ipapi.co/json/").always(function(resp) {
					var countryCode = (resp && resp.country) ? resp.country : "US";
					success(countryCode);
					
				}).fail(function() {
					console.log("ip lookup is not working.");
				});
			}});
		}
		
		vars['sa_intellinput_'+i] = jQuery(this).intlTelInput(object);
		var itis = vars['sa_intellinput_'+i];		
		if(default_cc!='')
		{
			var selected_cc = getCountryByCode(default_cc);
			var show_default_cc = selected_cc[0].iso2.toUpperCase();
			itis.intlTelInput("setCountry",show_default_cc);
		}
	});	
	
	//get all country data		
	function getCountryByCode(code) {
		return window.intlTelInputGlobals.getCountryData().filter(
		function(data){ return (data.dialCode == code) ? data.iso2 : ''; }
		);
	}

	jQuery('#billing_country').change(function(){
		var iti = vars[jQuery("#billing_phone").attr('data-id')];
		iti.intlTelInput("setCountry",$(this).val());
		onChangeCheckValidno(document.querySelector("#billing_phone"));
	});

	var reset = function() {
        jQuery(".sa_phone_error").text("");
    };	

	function onChangeCheckValidno(obj)
	{
		reset();
		var input 	= obj;
		//var iti 	= vars[jQuery(obj).attr('data-id')]; // 04/01/2020
		var iti 	= jQuery(obj);
		if (input.value.trim()) {
			if (iti.intlTelInput('isValidNumber')) {
				jQuery("#smsalert_otp_token_submit").attr("disabled",false);
				jQuery("#sa_bis_submit").attr("disabled",false);
            } else {
                var errorCode = iti.intlTelInput('getValidationError');
                input.focus();
                $(obj).parents(".iti--separate-dial-code").next(".sa_phone_error").text(errorMap[errorCode]);
				jQuery("#smsalert_otp_token_submit").attr("disabled",true);
				$(obj).parents(".iti--separate-dial-code").next(".sa_phone_error").removeAttr("style");
				jQuery("#sa_bis_submit").attr("disabled",true);
			}
        }
	}

    jQuery(document).on("blur","#billing_phone, .phone-valid",function() {
		onChangeCheckValidno(this);
    });

	//backinstock form
	// jQuery('.sa_bis_submit, .sa-otp-btn-init, form.register input[type=submit],input[type=submit]').click(function(){
		// var ph_field = jQuery(this).parents("form").find(".phone-valid");
		// if(typeof ph_field.val()=='undefined')
		// {
			// var ph_field = jQuery(this).parents("form").find("#billing_phone");
		// }

		// if(typeof ph_field.val()!='undefined')
		// {
			// ph_field.val(ph_field.intlTelInput("getNumber").replace(/\D/g, ""));
		// }
	// });
		
	jQuery(".phone-valid,#billing_phone").keyup(function(){
		var fullnumber =  jQuery(this).intlTelInput("getNumber"); //get number with std code
		jQuery(this).intlTelInput("setNumber",fullnumber);
		jQuery(this).next("[name=billing_phone]").val(fullnumber);
	});
	
	jQuery(".phone-valid,#billing_phone").trigger('keyup');

	// on keyup / change flag: reset
    jQuery("#billing_phone").change(function() {
		reset();
    });
});