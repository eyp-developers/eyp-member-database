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

        var dom_menu_item = $('<li class="dropdown">'+
          '<a href="#" class="dropdown-toggle" data-toggle="dropdown">' + menu_item.title + ' <b class="caret"></b></a>' +
          '<ul class="dropdown-menu navmenu-nav" role="menu">' +
            '<li><a href="#" class="menu-item">All People</a></li>' +
            '<ul class="dropdown-menu navmenu-nav" role="menu">' +
              '<li><a href="#" class="menu-item">Test entry</a></li>' +
            '</ul>' +
          '</ul>' +
        '</li>');

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