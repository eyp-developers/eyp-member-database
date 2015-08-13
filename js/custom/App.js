/**
 * Initialization
 */
function init() {
    // Navigation support
    Navigation.setupListener();

    // Load configuration
    UIComponents.loadingMask($('#sidebar-main-menu'));
    $.ajax({
        dataType: "json",
        url: "/backend/config",
        success: function(config) {
            // Load stores
            if(typeof config.stores !== 'undefined') {
                for(i in config.stores) {
                    var store = config.stores[i];
                    Stores.load(store.module_name, store.name);
                }
            }

            // Apply sidebar
            if(typeof config.sidebar !== 'undefined') {
                UI.applySidebarConfig(config.sidebar);
            }
        }
    });
};

$(document).ready(init);