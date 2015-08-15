/**
 * App
 */
 var App = 
 {
    applySettings : function(settings) {
        // Apply app settings
        if(typeof settings.app_settings !== 'undefined') {
            var app_settings = settings.app_settings;

            // Title
            if(typeof app_settings.app_title !== 'undefined') {
                $('#app-title').html(app_settings.app_title);
                document.title = app_settings.app_title;
            }
        }

        // Load stores
        if(typeof settings.stores !== 'undefined') {
            for(i in settings.stores) {
                var store = settings.stores[i];
                Stores.load(store.module_name, store.name);
            }
        }

        // Apply sidebar
        if(typeof settings.sidebar !== 'undefined') {
            UI.applySidebarConfig(settings.sidebar);
        }
    },

    init : function() {
        // Navigation support
        Navigation.setupListener();

        // Load configuration
        UIComponents.loadingMask($('#sidebar-main-menu'));
        $.ajax({
            dataType: "json",
            url: "/backend/settings",
            success: function(response) {
                App.applySettings(response);
            },
            error: function(response) {
                UI.showAlert('danger', 'Failed to load App configuration!');
            }
        });
    }
}

// Start the app when the document is ready
$(document).ready(App.init);