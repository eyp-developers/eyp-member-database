/**
 * Helpers
 */
var Helpers =
{
    replacePlaceholdersInURL: function(target, values) {
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