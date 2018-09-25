(function ($) {

    "use strict";

    console.log('sso');

    // get query vars function
    function getQueryParams(qs) {
        qs = qs.split("+").join(" ");
        var params = {},
            tokens,
            re = /[?&]?([^=]+)=([^&]*)/g;
        while (tokens = re.exec(qs)) {
            params[decodeURIComponent(tokens[1])]
                = decodeURIComponent(tokens[2]);
        }
        return params;
    }

    // get query vars
    var getter = getQueryParams(document.location.search);
    if (typeof(getter.sso) != 'undefined') {
        if (typeof(getter.u) != 'undefined') {
            $('#user_login').val(getter.u)
        }
    }

    // login form
    $(function () {

        var $wpSubmit = $('#wp-submit');
        var $wpLoginForm = $('#loginform');

        if (pssoOption.dist == 'on') {
            // disable the login submit button unless autocomplete field is filled in
            $wpSubmit.attr('disabled', 'disabled');
        }

        // autocomplete field
        $('#district-ac').autocomplete({
            source: ssoDistricts,
            focus: function (event, ui) {
                // prevent autocomplete from updating the textbox
                event.preventDefault();
                // manually update the textbox
                $(this).val(ui.item.label);
                console.log($(this).val(ui.item.label));
            },
            select: function (event, ui) {
                // prevent autocomplete from updating the textbox
                event.preventDefault();
                // manually update the textbox and hidden field
                $(this).val(ui.item.label);
                $("#district").val(ui.item.value);
                $('.providers-radio').hide();
                $('*[data-env="' + ui.item.value + '"]').show();
                $(this).blur();
            }
        });

        // when the focus changes in login form, check to see that there is a username, password, and value for the district

        var $blurred = $wpLoginForm.find("input");

        $blurred.blur(function () {
            var empty = $blurred.filter(function () {
                return this.value === "";
            });
            if (empty.length && pssoOption.dist == 'on') {
                $wpSubmit.attr('disabled', 'disabled');
            } else if (pssoOption.dist == 'on') {
                $wpSubmit.removeAttr('disabled');
                $wpSubmit.focus();
            }
        });


        // allow enter key to submit form
        /*
         $wpLoginForm.keydown(function (e) {
         var keyCode = e.keyCode || e.which;
         if (keyCode == 13) {
         $(this).submit();
         return false;
         }
         });*/

    });

}(jQuery));