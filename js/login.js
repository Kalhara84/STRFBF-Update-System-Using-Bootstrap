// Check the login form in the browser before sending it to the server
/**
  * Basic jQuery Validation Form Demo Code
  * Copyright Sam Deering 2012
  * Licence: http://www.jquery4u.com/license/
  */
(function($,W,D)
{
    var JQUERY4U = {};

    JQUERY4U.UTIL =
    {
        setupFormValidation: function()
        {
            // Rules: user name and password must be filled
            $("#login").validate({
                rules: {
                    usdusd: "required",
                    usdpwd: {
                        required: true,
                        minlength: 1
                    },
                    agree: "required"
                },
                // Messages shown when a rule fails
                messages: {
                    usdusd: "Please enter User Code",
                    usdpwd: {
                        required: "Please provide a password",
                        minlength: "Your password must be at least 1 characters long"
                    },
                },
                // When all fields are valid, send the form
                submitHandler: function(form) {
                    form.submit();
                }
            });
        }
    }

    // When the page is ready, start the form validation
    $(D).ready(function($) {
        JQUERY4U.UTIL.setupFormValidation();
    });

})(jQuery, window, document);
