/**
 * Helpers
 */
var Helpers =
{
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

        // Include checkboxes
        var checkbox_values = form.find('input[type=checkbox]').map(function() {
            return {"name": this.name, "value": (this.checked ? 1 : 0)}
        }).get();

        var indexed_array = {};

        $.map(unindexed_array, function(n, i){
            indexed_array[n['name']] = n['value'];
        });

        $.map(checkbox_values, function(n, i){
            indexed_array[n['name']] = n['value'];
        });

        return indexed_array;
    },

    getInputTypeForDataType: function(data_type) {
        switch(data_type) {
            case 'email':
            case 'date':
            case 'password':
                return data_type;

            default:
                return 'text';
        }
    },

    getDateFormatted: function(date) {
        var dd = date.getDate();
        var mm = date.getMonth()+1; //January is 0!
        var yyyy = date.getFullYear();

        if(dd<10) {
            dd='0'+dd
        } 

        if(mm<10) {
            mm='0'+mm
        } 

        date = yyyy+'-'+mm+'-'+dd;
        return date;
    }
};