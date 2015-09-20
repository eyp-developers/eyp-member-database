/**
 * Server
 */
var Server =
{
	ajax: function(config) {
        // Extract error callbacks
        var fn_success = $.noop;
        var fn_error = $.noop;

        if(typeof config.success === 'function') {
            fn_success = config.success;
        }

        if(typeof config.error === 'function') {
            fn_error = config.error;
        }

        config.error = function(response, textStatus, error) {
            UI.showAlert('danger', 'The server responded with an error (' + response.status + ')');
        };

        config.success = function(response, textStatus, jqXHR) {
            if(response.success === false) {
                if(response.error === Constants.E_NEED_SETUP) {
                    Installer.init();
                } else if (response.error === Constants.E_NOT_LOGGED_IN) {
                    UI.showLogin();
                } else if (response.error === Constants.E_MISSING_PERMISSION) {
                    UI.showAlert('danger', 'You do not have the permission to perform this action');
                } else {
                    fn_error.call(this, response, textStatus, jqXHR);
                }

                return;
            }

            fn_success.call(this, response, textStatus, jqXHR);
        }

        // Set auth token
        var auth_token = localStorage.getItem('auth_token');

        config.headers = {
            'auth_token' : auth_token
        };

        // Perform the request
        $.ajax(config);
    },
};