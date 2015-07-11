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

        // Build header
        if(title && title.length != 0) {
            var dom_header = $('<h1 class="page-header">' + title + '</h1>');
            target.append(dom_header);
        }

        // Extract fields
        var fields = new Array;
        for(var column_id in columns) {
            fields.push(columns[column_id]["field"]);
        }

        // Build column config for table
        var columns_config = new Array();
        for(c_index in columns) {
            var column = columns[c_index];

            // Copy basic values
            var column_config = {
                'field' : column.field,
                'title' : column.title,
                "visible" : (column.visible !== undefined ? column.visible : true),
                "sortable" : (column.sortable !== undefined ? column.sortable : true)
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

                case 'plain' :
                    break;

                default:
                    console.error('Unsupported column type "' + column.type + '"');
            }

            // Add column to table
            columns_config.push(column_config);
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
    }
}

var UI =
{
    applyViewConfig : function(config) {

        // Get the main view
        var main = $("#main");
        main.html('');

        // Handle the type of the view
        switch(config.type) {
            case 'table':
                UIComponents.table(config.title, config.datasource, config.columns, main);
                break;
            default:
                console.error('Unsupported view type "' + config.type + '"');
        }
    },

    applySidebarConfig : function(sidebar_config) {
        // Load sidebar items
        var sidebar_main_menu = $("#sidebar-main-menu");

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
    email : function(value, row, column) {
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

            // Load the view config and handle it
            $.ajax({
              dataType: 'json',
              url: '/backend/modules/' +  target[1] + '/views/' + target[2],
              success: UI.applyViewConfig
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