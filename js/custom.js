/**
 * User Interface
 */
var UIComponents = {
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

    table : function(title, datasource, columns) {
        var container = $('<div></div>');

        // Build header
        if(title && title.length != 0) {
            var dom_header = $('<h1 class="page-header">' + title + '</h1>');
            container.append(dom_header);
        }

        // Extract fields
        var fields = new Array;
        for(var column_id in columns) {
            fields.push(columns[column_id]["field"]);
        }

        // Build table
        var dom_table = $('<table id="main-table"></table>');
        dom_table.bootstrapTable({
            url: datasource,
            columns: columns,
            queryParams: function(params) {
                params.fields = fields.join();
                return params;
            }
        });
        container.append(dom_table);

        return container;
    }
}

var UIHelper =
{
    applyViewConfig : function(config) {

        // Get the main view
        var main = $("#main");

        // Handle the type of the view
        switch(config.type) {
            case 'table':
                main.html(UIComponents.table(config.title, config.datasource, config.columns));
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
             NavHelper.navigateToURL(this.href);
        });
    },
};

/**
 * Navigation
 */

var NavHelper = 
{
    navigateToHome : function() {
        // TODO navigate home...
        console.error("Not implemented yet: NavHelper.navigateToHome()");
    },

    navigateToURL : function(url) {
        // Make sure we are navigating locally
        if(!url.indexOf('#') == -1) {
            window.location = url;
        }

        // Extract the target from the URL
        var target = url.split('#')[1];

        if(target.length == 0) {
            NavHelper.navigateToHome();
            return;
        }

        // Split the target into model and view
        target = target.split('/')
        if(target.length >= 3) {

            // Load the view config and handle it
            $.ajax({
              dataType: 'json',
              url: '/backend/modules/' +  target[1] + '/views/' + target[2],
              success: UIHelper.applyViewConfig
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
                UIHelper.applySidebarConfig(config.sidebar);
            }
        }
    });
}

$(document).ready(init);