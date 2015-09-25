/**
 * Stores
 */

 var Stores =
 {
    data : {},

    getStore : function(module_name, store_name) {
        if(typeof this.data[module_name] !== 'undefined' && typeof this.data[module_name][store_name] !== 'undefined') {
            return this.data[module_name][store_name];
        }
        return null;
    },

    getValueForStoreAndKey : function(module_name, store_name, key) {
        var store = this.getStore(module_name, store_name);
        if(store === null) {
            return null;
        }
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

    haveStoresForModule : function(module_name) {
        return (typeof Stores.data[module_name] !== 'undefined' && Stores.data[module_name] !== null);
    },

    loadStoresForModule : function(module_name) {
        Stores.data[module_name] = {};

        Server.ajax({
            dataType: "json",
            url: "/backend/modules/"+module_name+'/stores',
            success: function(response) {
                var stores = response.data;

                for(var i = 0; i < stores.length; i++) {
                    var store = stores[i];

                    Stores.data[module_name][store.name] = store;
                }
            }
        });
    },

    load : function(module_name, store_name) {
        Server.ajax({
            dataType: "json",
            url: "/backend/modules/"+module_name+'/stores/'+store_name,
            success: function(response) {
                var store = response.data;
                
                if(typeof Stores.data[module_name] === 'undefined') {
                    Stores.data[module_name] = {};
                }
                Stores.data[module_name][store_name] = store;
            }
        });
    }
};