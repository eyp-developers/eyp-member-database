/**
 * Stores
 */

 var Stores =
 {
    data : {},

    getStore : function(module_name, store_name) {
        return this.data[module_name][store_name];
    },

    getValueForStoreAndKey : function(module_name, store_name, key) {
        var store = this.getStore(module_name, store_name);
        return store.data[key];
    },

    reloadStoresForModuleAndModel : function(module_name, model_name) {
        var module_stores = this.data[module_name];
        for(i in module_stores) {
            var store = module_stores[i];

            if(store.model_name === model_name) {
                this.load(store.module_name, store.name);
            }
        }
    },

    load : function(module_name, store_name) {
        $.ajax({
            dataType: "json",
            url: "/backend/modules/"+module_name+'/stores/'+store_name,
            success: function(store) {
                if(typeof Stores.data[module_name] === 'undefined') {
                    Stores.data[module_name] = {};
                }
                Stores.data[module_name][store_name] = store;
            }
        });
    }
};