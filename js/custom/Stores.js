/**
 * Stores
 */

 var Stores =
 {
    data : {},

    getStore : function(module_name, store_name) {
        return this.data[module_name + '_' + store_name];
    },

    getValueForStoreAndKey : function(module_name, store_name, key) {
        var store = this.getStore(module_name, store_name);
        return store.data[key];
    },

    load : function(module_name, store_name) {
        $.ajax({
            dataType: "json",
            url: "/backend/modules/"+module_name+'/stores/'+store_name,
            success: function(store) {
                Stores.data[module_name + '_' + store_name] = store;
            }
        });
    }
};