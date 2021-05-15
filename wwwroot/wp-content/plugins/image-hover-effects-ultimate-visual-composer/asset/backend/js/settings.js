jQuery.noConflict();
(function ($) {
    var styleid = '';
    var childid = '';
    function Oxi_Flip_Admin_Settings(functionname, rawdata, styleid, childid, callback) {
        if (functionname !== "") {
            $.ajax({
                url: oxi_flip_box_settings.ajaxurl,
                type: "post",
                data: {
                    action: "oxi_flip_box_data",
                    _wpnonce: oxi_flip_box_settings.nonce,
                    functionname: functionname,
                    styleid: styleid,
                    childid: childid,
                    rawdata: rawdata
                },
                success: function (response) {
                    callback(response);
                }
            });
        }
    }
    function delay(callback, ms) {
        var timer = 0;
        return function () {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                callback.apply(context, args);
            }, ms || 0);
        };
    }

    $("input[name=oxilab_flip_box_license_key] ").on("keyup", delay(function (e) {
        var $This = $(this), $value = $This.val();
        if ($value !== $.trim($value)) {
            $value = $.trim($value);
            $This.val($.trim($value));
        }
        var rawdata = JSON.stringify({license: $value});
        var functionname = "oxi_license";
        $('.oxilab_flip_box_license_massage').html('<span class="spinner sa-spinner-open"></span>');
        Oxi_Flip_Admin_Settings(functionname, rawdata, styleid, childid, function (callback) {
            var callback = jQuery.parseJSON(callback);
            $('.oxilab_flip_box_license_massage').html(callback.massage);
            $('.oxilab_flip_box_license_text .oxi-addons-settings-massage').html(callback.text);
        });
    }, 1000));




    $(document.body).on("click", "input", function (e) {

        var $This = $(this), name = $This.attr('name'), $value = $This.val();
        var rawdata = JSON.stringify({name: name, value: $value});
        if (name === 'oxilab_flip_box_license_key') {
            return;
        }
        var functionname = "oxi_settings";
        $('.' + name).html('<span class="spinner sa-spinner-open"></span>');
        Oxi_Flip_Admin_Settings(functionname, rawdata, styleid, childid, function (callback) {
            $('.' + name).html(callback);
            setTimeout(function () {
                $('.' + name).html('');
            }, 8000);
        });
    });
    $(document.body).on("change", "select", function (e) {
        var $This = $(this), name = $This.attr('name'), $value = $This.val();
        var rawdata = JSON.stringify({name: name, value: $value});
        var functionname = "oxi_settings";
        $('.' + name).html('<span class="spinner sa-spinner-open"></span>');
        Oxi_Flip_Admin_Settings(functionname, rawdata, styleid, childid, function (callback) {
            $('.' + name).html(callback);
            setTimeout(function () {
                $('.' + name).html('');
            }, 8000);
        });
    });



})(jQuery)