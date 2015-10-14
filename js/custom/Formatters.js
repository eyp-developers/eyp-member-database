/**
 * Formatters
 */
var Formatters = 
{
    email : function() {
        return function(value, row, index) {
            // In case this gets called for an invisible column
            if(!this.visible || value === null || typeof value === 'undefined') return '';

            return '<a href="mailto:' + value + '">' + value + '</a>';
        }
    },

    link : function() {
        return function(value, row, index) {
            // In case this gets called for an invisible column
            if(!this.visible || value === null || typeof value === 'undefined') return '';

            // Check if this is an external target
            if(!this.target) {
                if(value.indexOf('http://') === 0 ||
                   value.indexOf('https://') === 0 ||
                   value.indexOf('ftp://') === 0) {
                    return '<a href="' + value + '" >' + value + '</a>';
                } else {
                    return '<a href="http://' + value + '" >' + value + '</a>';
                }
            }

            // Replace placeholders in target
            var target_parts = this.target.split('/');
            for(part_index in target_parts) {
                if(target_parts[part_index].indexOf(":") == 0) {
                    var key = target_parts[part_index].slice(1);
                    if(row[key] !== undefined) {
                        target_parts[part_index] = row[key];
                    } else {
                        console.error('Column target is referring to undefined key "' + key + '"');
                    }
                }
            }
            return '<a href="#' + target_parts.join('/') + '">' + value + '</a>';
        }
    },

    action : function() {
        return function(value, row, index) {
            // In case this gets called for an invisible column
            if(!this.visible || value === null) return '';
            
            var html = new Array();

            for(action_id in this.actions) {
                var action = this.actions[action_id];

                // Replace placeholders in target
                var real_target = Helpers.replacePlaceholdersInURL(action.target, row);

                html.push(
                    '<a class="like" href="#' + real_target + '">',
                        '<i class="glyphicon glyphicon-' + action.icon + '"></i>',
                    '</a>'
                );
            }

            return html.join(' ');
        }
    },

    store : function(store_module, store_name, next_formatter) {
        return function(value, row, index) {
            // In case this gets called for an invisible column
            if(value === null || typeof value === 'undefined') return '';

            value = Stores.getValueForStoreAndKey(store_module, store_name, value);
            if(typeof next_formatter === 'function') {
                return next_formatter.call(this, value, row, index);
            } else {
                return value;
            }
        };
    },

    plain : function() {
        return function(value, row, index) {
            return value;
        }
    },

    boolean : function() {
        return function(value, row, index) {
            if(value == 1) {
                return '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
            } else {
                return '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>';
            }
        }
    }
};