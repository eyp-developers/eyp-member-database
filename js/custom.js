/**
 * UI Components
 */

var UIHelper =
{
    sidebarDropdown : function(title, items) {
        // Generate dropdown and menu
        var dom_dropdown = $('<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">' + title + ' <b class="caret"></b></a></li>');
        var dom_menu = $('<ul class="dropdown-menu navmenu-nav" role="menu"></ul>');

        // Generate menu entries
        for(item_id in items) {
            var menu_item = items[item_id];
            var dom_menu_item = $('<li><a href="#/view/' + menu_item.view_name + '" class="menu-item">' + menu_item.view_title + '</a></li>');

            // Append menu entry
            dom_menu.append(dom_menu_item);
        }

        // Append menu
        dom_dropdown.append(dom_menu);

        return dom_dropdown;
    }
};

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
                initSidebar(config.sidebar);
            }
        }
    });
}

// Initilize the sidebar
function initSidebar(sidebar_config) {
    // Load sidebar items
    var sidebar_main_menu = $("#sidebar-main-menu");

    for(var menu_index in sidebar_config) {
        var menu_item = sidebar_config[menu_index];

        var dom_menu_item = UIHelper.sidebarDropdown(menu_item.title, menu_item.items);

        sidebar_main_menu.append(dom_menu_item);
    }

    $(".menu-item").click(function() {
        // Switch active sidebar item
        $("li.active").removeClass("active");
        $(this).parent("li").addClass("active");

        $.ajax({
          dataType: "json",
          url: "/backend/modules/people/views/all_people",
          success: handleViewConfig
        });

    });
}

function handleViewConfig(config) {
    // DEBUG
    console.log(config);

    // Extract fields
    var fields = new Array;
    for(var column_id in config.columns) {
        fields.push(config.columns[column_id]["field"]);
    }

    // Load Table
    $('#table').bootstrapTable({
        url: config.datasource,
        columns: config.columns,
        queryParams: function(params) {
            params.fields = fields.join();
            console.log(params);
            return params;
        }
    });
}

// Initialize JS once the document is loaded
$(document).ready(init);