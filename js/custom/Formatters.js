/**
 * Formatters
 */
var Formatters = 
{
    email : function(value, row, index) {
        // In case this gets called for an invisible column
        if(!this.visible || value === null) return '';

        return '<a href="mailto:' + value + '">' + value + '</a>';
    },

    link : function(value, row, index) {
        // In case this gets called for an invisible column
        if(!this.visible || value === null) return '';

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
    },

    action : function(value, row, index) {
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
};