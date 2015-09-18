/**
 * Helpers
 */
var Helpers =
{
    ajax: function(config) {
        // Extract error callbacks
        var fn_error = $.noop;
        if(typeof config.error !== 'undefined' && config.error !== null) {
            fn_error = config.error;
        }

        config.error = function(response, textStatus, error) {
            // Check if we need to log in
            if(response.status === 401) {
                UI.showLogin();
            } else {
                fn_error(response, textStatus, error);
            }
        };

        // Set auth token
        var authToken = localStorage.getItem('authToken');

        if(typeof authToken === 'undefined' || authToken === null) {
            UI.showLogin();
            return;
        }

        config.headers = {
            'AuthToken' : authToken
        };

        // Perform the request
        $.ajax(config);
    },

    replacePlaceholdersInURL: function(target, values) {
        if(target === null || target === '') {
            return '';
        }

        var target_parts = target.split('/');

        // Check if we have a dictionary or an array of values
        if(values instanceof Array) {
            var key = 0;
            for(part_index in target_parts) {
                if(target_parts[part_index].indexOf(":") == 0) {
                    if(values[key] !== undefined) {
                        target_parts[part_index] = values[key];
                    } else {
                        console.error('Placeholder is referring to undefined key "' + key + '"');
                    }
                }
            }
        } else {
            for(part_index in target_parts) {
                if(target_parts[part_index].indexOf(":") == 0) {
                    var key = target_parts[part_index].slice(1);
                    if(values[key] !== undefined) {
                        target_parts[part_index] = values[key];
                    } else {
                        console.error('Placeholder is referring to undefined key "' + key + '"');
                    }
                }
            }
        }

        return target_parts.join('/');
    },

    getFormData: function(form) {
        var unindexed_array = form.serializeArray();
        var indexed_array = {};

        $.map(unindexed_array, function(n, i){
            indexed_array[n['name']] = n['value'];
        });

        return indexed_array;
    },

    getInputTypeForDataType: function(data_type) {
        switch(data_type) {
            case 'email':
                return 'email';

            case 'date':
                return 'date';

            default:
                return 'text';
        }
    }
};