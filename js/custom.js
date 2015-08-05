/**
 * User Interface
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
            'formatter' : Formatter.action,
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
                    column_config.formatter = Formatter.link;
                    break;

                case 'email' : 
                    column_config.formatter = Formatter.email;
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

                        // Apply renderer if needed
                        if(field.type !== null && field.type !== '') {
                            dd = Formatter[field.type].call(field, data[field.data_key], data, null);  
                        } else {
                            dd = data[field.data_key];
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
            var target = Helper.replacePlaceholdersInURL(field.data_key, params)
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
}

var UI =
{
    applyViewConfig : function(config, params, dom_target) {

        // Get the main view if no target was defined
        if(typeof dom_target === 'undefined' || dom_target === null) {
            dom_target = $("#main");
        }

        // Handle the type of the view
        switch(config.type) {
            case 'table':
                var datasource = Helper.replacePlaceholdersInURL(config.datasource, params);
                UIComponents.table(config.title, datasource, config.fields, dom_target);
                break;

            case 'detail':
                var datasource = Helper.replacePlaceholdersInURL(config.datasource, params);
                UIComponents.detail(config.title, datasource, config.fields, dom_target);
                break;

            case 'combined':
                UIComponents.combined(config.title, params, config.fields, dom_target);
                break;

            default:
                console.error('Unsupported view type "' + config.type + '"');
        }
    },

    applySidebarConfig : function(sidebar_config) {
        // Load sidebar items
        var sidebar_main_menu = $("#sidebar-main-menu");
        sidebar_main_menu.html('');

        for(var menu_index in sidebar_config) {
            var menu_item = sidebar_config[menu_index];

            var dom_menu_item = UIComponents.sidebarDropdown(menu_item.title, menu_item.items);

            sidebar_main_menu.append(dom_menu_item);
        }

        // Handle Sidebar clicks
        $(".menu-item").click(function() {
            // Switch active sidebar item
            $("li.active").removeClass("active");
            $(this).parent("li").addClass("active");

            // Load view config
             Navigation.navigateToURL(this.href);
        });
    },
};

var Formatter = 
{
    email : function(value, row, index) {
        // In case this gets called for an invisible column
        if(!this.visible) return;

        return '<a href="mailto:' + value + '">' + value + '</a>';
    },

    link : function(value, row, index) {
        // In case this gets called for an invisible column
        if(!this.visible) return;

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
        return '<a href="#' + target_parts.join('/') + '" onclick="Navigation.navigateToURL(this.href)">' + value + '</a>';
    },

    action : function(value, row, index) {
        // In case this gets called for an invisible column
        if(!this.visible) return;
        
        var html = new Array();

        for(action_id in this.actions) {
            var action = this.actions[action_id];

            // Replace placeholders in target
            var real_target = Helper.replacePlaceholdersInURL(action.target, row);

            html.push(
                '<a class="like" href="#' + real_target + '" onclick="Navigation.navigateToURL(this.href)" title="Like">',
                    '<i class="glyphicon glyphicon-' + action.icon + '"></i>',
                '</a>'
            );
        }

        return html.join(' ');
    }
}

/**
 * Helper
 */
var Helper =
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
    }
}

/**
 * Navigation
 */

var Navigation = 
{
    navigateToHome : function() {
        // TODO navigate home...
        console.error("Not implemented yet: Navigation.navigateToHome()");
    },

    navigateToURL : function(url) {
        // Make sure we are navigating locally
        if(!url.indexOf('#') == -1) {
            window.location = url;
        }

        // Extract the target from the URL
        var target = url.split('#')[1];

        if(target.length == 0) {
            Navigation.navigateToHome();
            return;
        }

        // Split the target into model and view
        target = target.split('/')
        if(target.length >= 3) {

            UIComponents.loadingMask($('#main'));

            // Load the view config and handle it
            $.ajax({
              dataType: 'json',
              url: '/backend/modules/' +  target[1] + '/views/' + target[2],
              success: function(data) {
                  UI.applyViewConfig(data, target.slice(3));
              }
            });

        } else {
            console.error('URL "' + url + '" is not a valid target!');
        }
    }
}

/**
 * Initialization
 */

// Initialize JS
function init() {
    // Load configuration
    UIComponents.loadingMask($('#sidebar-main-menu'));
    $.ajax({
        dataType: "json",
        url: "/backend/config",
        success: function(config) {
            if(config.sidebar) {
                UI.applySidebarConfig(config.sidebar);
            }
        }
    });
}

$(document).ready(init);