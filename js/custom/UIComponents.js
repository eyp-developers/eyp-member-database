/**
 * UI Components 
 */
var UIComponents =
{
    sidebarDropdown : function(title, items) {
        // Generate dropdown and menu
        var dom_dropdown = $('<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">' + title + ' <b class="caret"></b></a></li>');
        var dom_menu = $('<ul class="dropdown-menu navmenu-nav" role="menu"></ul>');

        // Generate menu entries
        for(item_id in items) {
            var menu_item = items[item_id];
            var dom_menu_item = $('<li><a href="#' + menu_item.target + '" class="menu-item">' + menu_item.title + '</a></li>');

            // Append menu entry
            dom_menu.append(dom_menu_item);
        }

        // Append menu
        dom_dropdown.append(dom_menu);

        return dom_dropdown;
    },

    table : function(title, datasource, columns, target) {

        // Clear the target
        target.html('');

        // Build header
        if(title && title.length != 0) {
            var dom_header = $('<h1 class="page-header">' + title + '</h1>');
            target.append(dom_header);
        }

        // Extract fields
        var fields = new Array;
        for(var column_id in columns) {
            if(columns[column_id]["data_key"] !== undefined
               && columns[column_id]["data_key"] !== null
               && columns[column_id]["data_key"].length !== 0)
                fields.push(columns[column_id]["data_key"]);
        }

        // Group up action columns
        var action_column = {
            'formatter' : Formatters.action,
            'sortable' : false,
            'actions' : []
        };

        // Build column config for table
        var columns_config = new Array();
        for(c_index in columns) {
            var column = columns[c_index];

            // Copy basic values
            var column_config = {
                'field' : column.data_key,
                'title' : column.title,
                'visible' : (column.visible == true ? true : false),
                'sortable' : (column.sortable !== undefined ? column.sortable : true)
            }

            // Apply type to column
            if(!column.type) column.type = 'plain';
            switch(column.type) {
                case 'link' :
                    column_config.target = column.target;
                    column_config.formatter = Formatters.link;
                    break;

                case 'email' : 
                    column_config.formatter = Formatters.email;
                    break;

                case 'action' :
                    action_column.actions.push({
                        'target' : column.target,
                        'icon' : column.icon
                    });
                    break;

                case 'int' :
                case 'plain' :
                    break;

                default:
                    console.error('Unsupported column type "' + column.type + '"');
            }

            // Add column to table
            if(column.type !== 'action') {
                columns_config.push(column_config);
            }
        }

        // Add action column
        if(action_column.actions.length > 0) {
            columns_config.push(action_column);
        }

        // Build table
        var dom_table = $('<table id="main-table"></table>');
        target.append(dom_table);

        dom_table.bootstrapTable({
            url: datasource,
            columns: columns_config,
            sortable: true,
            pagination: true,
            pageSize: 20,
            sidePagination: 'server',
            search: true,
            queryParams: function(params) {
                params.fields = fields.join();
                return params;
            }
        });
    },

    detail : function(title, datasource, fields, dom_target) {

        // Clear the target
        dom_target.html('');

        // Build header
        if(title && title.length != 0) {
            var dom_header = $('<h1 class="page-header">' + title + '</h1>');
            dom_target.append(dom_header);
        }

        // Generate container for the detail view
        var dl_target = $('<div class="row"></div>');
        dom_target.append(dl_target);
    
        // Show loading mask
        UIComponents.loadingMask(dl_target);

        // Send a request for data
        $.ajax({
            dataType: 'json',
            url: datasource,
            success: function(data) {

                // Prepare the definition list
                var html = $('<dl class="dl-horizontal"></dl>');

                // Go through all the fields
                for(field_id in fields) {
                    var field = fields[field_id];

                    if(field.visible != true) {
                        continue;
                    }

                    var dt = '';
                    var dd = '-';
                    
                    if(field.title !== undefined
                       && field.title !== null) {
                        dt = field.title;
                    }

                    if(data[field.data_key] !== undefined
                       && data[field.data_key] !== null) {

                        var value = data[field.data_key];
                        if(typeof field.store_module !== 'undefined'
                           && typeof field.store_name !== 'undefined'
                           && field.store_module !== null
                           && field.store_name !== null) {
                            value = Stores.getValueForStoreAndKey(field.store_module, field.store_name, value);
                        }

                        // Apply renderer if needed
                        if(field.type !== null && field.type !== '') {
                            dd = Formatters[field.type].call(field, value, data, null);  
                        } else {
                            dd = value;
                        }
                    }

                    html.append($('<dt>' + dt + '</dt><dd>' + dd + '</dd>'));
                }

                dl_target.html(html);
            }
        });
    },

    combined : function(title, params, fields, dom_target) {

        // Clear the target
        dom_target.html('');

        // Build header
        if(title && title.length != 0) {
            var dom_header = $('<h1 class="page-header">' + title + '</h1>');
            dom_target.append(dom_header);
        }

        // Generate container for the combined view
        var combined_target = $('<div></div>');
        dom_target.append(combined_target);

        // Show loading mask
        UIComponents.loadingMask(combined_target);

        // Iterate over all components
        for(field_id in fields) {
            var field_dom = $('<div></div>');
            combined_target.append(field_dom);

            var field = fields[field_id];

            // Split the target into model and view
            var target = Helpers.replacePlaceholdersInURL(field.data_key, params)
            target = target.split('/')
            if(target.length >= 3) {

                // Load the view config and handle it
                var first_component = true;
                $.ajax({
                    dataType: 'json',
                    url: '/backend/modules/' +  target[1] + '/views/' + target[2],
                    view_params: target,
                    field_dom: field_dom,
                    success: function(data) {
                        if(first_component) {
                            first_component = false;
                            combined_target.find('.spinner').remove();
                        }
                        UI.applyViewConfig(data, this.view_params.slice(3), this.field_dom);
                    }
                });

            } else {
                console.error('URL "' + url + '" is not a valid target!');
            }
        }
    },

    loadingMask : function(dom_target) {
        var loading_html = $([
            '<div class="spinner">',
                '<div class="double-bounce1"></div>',
                '<div class="double-bounce2"></div>',
            '</div>'].join(''));

        dom_target.html(loading_html);
    }
};