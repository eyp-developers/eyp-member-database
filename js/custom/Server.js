/**
 * Server
 */
var Server =
{
	ajax: function(config) {
        // Extract error callbacks
        var fn_success = $.noop;

        if(typeof config.success !== 'undefined' && config.error !== null) {
            fn_success = config.success;
        }

        config.error = function(response, textStatus, error) {
            UI.showAlert('danger', 'The server responded with an error (' + response.status + ')');
        };

        config.success = function(response, textStatus, jqXHR) {
            if(response.success === false) {
                if(response.need_setup === true) {
                    Installer.init();
                    return;
                } else if (response.need_login === true) {
                    UI.showLogin();
                    return;
                }
            }

            fn_success.call(this, response, textStatus, jqXHR);
        }

        // Set auth token
        var authToken = localStorage.getItem('authToken');

        config.headers = {
            'AuthToken' : authToken
        };

        // Perform the request
        $.ajax(config);
    },
};