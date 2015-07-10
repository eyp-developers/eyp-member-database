// Initialize JS
function init() {
    initNav();
}

// Initilize the Navigation functionality
function initNav() {
    // TODO Load menu items


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