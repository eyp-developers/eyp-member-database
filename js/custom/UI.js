/**
 * User Interface
 */
var UI =
{
    applyViewConfig : function(config, params, dom_target) {

        // Get the main view if no target was defined
        if(typeof dom_target === 'undefined' || dom_target === null) {
            if(typeof config.container !== 'undefined'
                && config.container !== null
                && config.container !== '') {
                dom_target = $('#' + config.container);
            } else {
                dom_target = $("#main");
            }
        }

        // Handle the type of the view
        switch(config.type) {
            case 'table':
                var datasource = Helpers.replacePlaceholdersInURL(config.datasource, params);
                UIComponents.table(config.title, datasource, config.fields, dom_target);
                break;

            case 'detail':
                var datasource = Helpers.replacePlaceholdersInURL(config.datasource, params);
                UIComponents.detail(config.title, datasource, config.fields, dom_target);
                break;

            case 'form':
                var datasource = Helpers.replacePlaceholdersInURL(config.datasource, params);
                UIComponents.form(config.title, datasource, config.fields, dom_target);
                break;

            case 'combined':
                UIComponents.combined(config.title, params, config.fields, dom_target);
                break;

            case 'dialog':
                UIComponents.dialog(config.title, params, config.fields, dom_target);
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

    showAlert : function(type, message) {
        // Verify type
        var alert_type = 'info'
        switch(type) {
            case 'success':
            case 'warning':
            case 'danger':
                alert_type = type;
                break;
        }

        // Generate alert
        var alert = $('<div class="alert alert-' + alert_type + '" role="alert" style="display: none;">' + message + '</div>');

        // Show alert
        $('#alerts').append(alert);
        alert.fadeIn(300).delay(3000).fadeOut(300, function() { $(this).remove(); });

    }
};