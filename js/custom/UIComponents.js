/**
 * UI Components 
 */
var UIComponents =
{
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

    table : function(title, datasource, columns, target) {

        // Clear the target
        target.html('');

        // Build header
        if(title && title.length != 0) {
            var dom_header = $('<h1 class="page-header">' + title + '</h1>');
            target.append(dom_header);
        }

        // Extract fields
        var fields = new Array;
        for(var column_id in columns) {
            if(columns[column_id]["data_key"] !== undefined
               && columns[column_id]["data_key"] !== null
               && columns[column_id]["data_key"].length !== 0)
                fields.push(columns[column_id]["data_key"]);
        }

        // Group up action columns
        var action_column = {
            'formatter' : Formatters.action,
            'sortable' : false,
            'actions' : []
        };

        // Build column config for table
        var columns_config = new Array();
        for(c_index in columns) {
            var column = columns[c_index];

            // Copy basic values
            var column_config = {
                'field' : column.data_key,
                'title' : column.title,
                'visible' : (column.visible == true ? true : false),
                'sortable' : (column.sortable !== undefined ? column.sortable : true)
            }

            // Apply type to column
            if(!column.type) column.type = 'plain';
            switch(column.type) {
                case 'link' :
                    column_config.target = column.target;
                    column_config.formatter = Formatters.link;
                    break;

                case 'email' : 
                    column_config.formatter = Formatters.email;
                    break;

                case 'action' :
                    action_column.actions.push({
                        'target' : column.target,
                        'icon' : column.icon
                    });
                    break;

                case 'int' :
                case 'plain' :
                    break;

                default:
                    console.error('Unsupported column type "' + column.type + '"');
            }

            // Add column to table
            if(column.type !== 'action') {
                columns_config.push(column_config);
            }
        }

        // Add action column
        if(action_column.actions.length > 0) {
            columns_config.push(action_column);
        }

        // Build table
        var dom_table = $('<table id="main-table"></table>');
        target.append(dom_table);

        dom_table.bootstrapTable({
            url: datasource,
            columns: columns_config,
            sortable: true,
            pagination: true,
            pageSize: 20,
            sidePagination: 'server',
            search: true,
            queryParams: function(params) {
                params.fields = fields.join();
                return params;
            }
        });
    },

    detail : function(title, datasource, fields, dom_target) {

        // Clear the target
        dom_target.html('');

        // Build header
        if(title && title.length != 0) {
            var dom_header = $('<h1 class="page-header">' + title + '</h1>');
            dom_target.append(dom_header);
        }

        // Generate container for the detail view
        var dl_target = $('<div class="row"></div>');
        dom_target.append(dl_target);
    
        // Show loading mask
        UIComponents.loadingMask(dl_target);

        // Send a request for data
        $.ajax({
            dataType: 'json',
            url: datasource,
            success: function(data) {

                // Prepare the definition list
                var html = $('<dl class="dl-horizontal"></dl>');

                // Go through all the fields
                for(field_id in fields) {
                    var field = fields[field_id];

                    if(field.visible != true) {
                        continue;
                    }

                    var dt = '';
                    var dd = '-';
                    
                    if(field.title !== undefined
                       && field.title !== null) {
                        dt = field.title;
                    }

                    if(data[field.data_key] !== undefined
                       && data[field.data_key] !== null) {

                        var value = data[field.data_key];
                        if(typeof field.store_module !== 'undefined'
                           && typeof field.store_name !== 'undefined'
                           && field.store_module !== null
                           && field.store_name !== null) {
                            value = Stores.getValueForStoreAndKey(field.store_module, field.store_name, value);
                        }

                        // Apply renderer if needed
                        if(field.type !== null && field.type !== '' && typeof(Formatters[field.type]) === 'function') {
                            dd = Formatters[field.type].call(field, value, data, null);  
                        } else {
                            dd = value;
                        }
                    }

                    html.append($('<dt>' + dt + '</dt><dd>' + dd + '</dd>'));
                }

                dl_target.html(html);
            }
        });
    },

    form : function(title, datasource, fields, dom_target) {
        
        // Clear the target
        dom_target.html('');

        // Build header
        if(title && title.length != 0) {
            var dom_header = $('<h1 class="page-header">' + title + '</h1>');
            dom_target.append(dom_header);
        }

        // Generate container for the detail view
        var dl_target = $('<div class="row"></div>');
        dom_target.append(dl_target);
    
        // Show loading mask
        UIComponents.loadingMask(dl_target);

        // Send a request for data
        $.ajax({
            dataType: 'json',
            url: datasource,
            success: function(data) {

                // Prepare the definition list
                var html = $('<form class="form-horizontal" action="' + datasource + '"></form>');

                // Go through all the fields
                for(field_id in fields) {
                    var field = fields[field_id];

                    var form_group = $('<div class="form-group"></div>');
                    
                    if(field.visible != true) {
                        form_group.css('display', 'none');
                    }

                    var label_text = '';
                    var label = '';
                    if(field.title !== undefined
                       && field.title !== null) {
                        label_text = field.title;
                    }

                    label = $('<label for="input_' + field.data_key + '" class="col-sm-3 control-label">' + label_text + '</label>'); 
                    form_group.append(label);

                    var input_text = '';
                    var input = '';
                    if(data[field.data_key] !== undefined
                       && data[field.data_key] !== null) {
                        var input_text = data[field.data_key];
                    }

                    switch(field.type) {

                        case 'textarea':
                            input = $('<div class="col-sm-8"><textarea rows="3" class="form-control" id="input_' + field.data_key + '" name="' + field.data_key + '" placeholder="' + label_text + '">' + input_text + '</textarea></div>');
                            break;

                        case 'select':
                            var select = $('<select class="form-control" id="input_' + field.data_key + '" name="' + field.data_key + '"></select>');
                            select.append($('<option value=""></option>'));
                            
                            var store = Stores.getStore(field.store_module, field.store_name);

                            for(key in store.data) {
                                if(key == input_text) {
                                    var option = $('<option value="' + key + '" selected>' + store.data[key] + '</option>');
                                } else {
                                    var option = $('<option value="' + key + '">' + store.data[key] + '</option>');
                                }
                                select.append(option);
                            }

                            input = $('<div class="col-sm-8"></div>');
                            input.append(select);
                            break;

                        default:
                            var input_type = Helpers.getInputTypeForDataType(field.type);
                            input = $('<div class="col-sm-8"><input type="' + input_type + '" class="form-control" id="input_' + field.data_key + '" name="' + field.data_key + '" placeholder="' + label_text + '" value="' + input_text + '"></div>');
                    }

                    form_group.append(input);

                    html.append(form_group);
                }

                var submit_button = $(
                    '<div class="form-group">'+
                        '<div class="col-sm-offset-3 col-sm-8">'+
                            '<button type="submit" class="btn btn-primary">Submit</button>'+
                        '</div>'+
                    '</div>'
                );
                html.append(submit_button);

                html.submit(function() {
                    var me = $(this);
                    var data = Helpers.getFormData(me);

                    $.ajax({
                        url: me.attr('action'),
                        data: JSON.stringify(data),
                        dataType: 'json',
                        type: 'POST',
                        success: function(response_data) {
                            UI.showAlert('success', 'Data was successfully saved!');
                            if(typeof response_data.db_changes !== 'undefined') {
                                for(i in response_data.db_changes) {
                                    var store_data = response_data.db_changes[i];
                                    Stores.reloadStoresForModuleAndModel(store_data.module_name, store_data.model_name);
                                }
                            }
                            
                            window.history.back();
                        },
                        error: function(response_data) {
                            UI.showAlert('danger', 'Could not save data!');
                        }
                    });

                    return false;
                })

                dl_target.html(html);
            }
        });
    },

    combined : function(title, params, fields, dom_target) {

        // Clear the target
        dom_target.html('');

        // Build header
        if(title && title.length != 0) {
            var dom_header = $('<h1 class="page-header">' + title + '</h1>');
            dom_target.append(dom_header);
        }

        // Generate container for the combined view
        var combined_target = $('<div></div>');
        dom_target.append(combined_target);

        // Show loading mask
        UIComponents.loadingMask(combined_target);

        // Iterate over all components
        for(field_id in fields) {
            var field_dom = $('<div></div>');
            combined_target.append(field_dom);

            var field = fields[field_id];

            // Split the target into model and view
            var target = Helpers.replacePlaceholdersInURL(field.data_key, params)
            target = target.split('/')
            if(target.length >= 3) {

                // Load the view config and handle it
                var first_component = true;
                $.ajax({
                    dataType: 'json',
                    url: '/backend/modules/' +  target[1] + '/views/' + target[2],
                    view_params: target,
                    field_dom: field_dom,
                    success: function(data) {
                        if(first_component) {
                            first_component = false;
                            combined_target.find('.spinner').remove();
                        }
                        UI.applyViewConfig(data, this.view_params.slice(3), this.field_dom);
                    }
                });

            } else {
                console.error('URL "' + url + '" is not a valid target!');
            }
        }
    },

    loadingMask : function(dom_target) {
        var loading_html = $([
            '<div class="spinner">',
                '<div class="double-bounce1"></div>',
                '<div class="double-bounce2"></div>',
            '</div>'].join(''));

        dom_target.html(loading_html);
    },

    dialog : function(title, params, fields, dom_target) {

        // Clear the target
        dom_target.html('');

        // Build header
        if(title && title.length != 0) {
            var dom_header = $('' +
                '<div class="modal-header">' +
                    '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<h4 class="modal-title">' + title + '</h4>' +
                '</div>');

            dom_target.append(dom_header);
        }

        // Generate container for the content and footer
        var content_target = $('<div class="modal-body"></div>');
        dom_target.append(content_target);

        var footer_target = $('<div class="modal-footer"></div>');
        dom_target.append(footer_target);

        // Iterate over all components
        for(field_id in fields) {

            var field = fields[field_id];

            switch(field.type) {
                case 'html':
                    content_target.append(field.title);
                    break;

                case 'button':
                    var button;
                    var button_target = Helpers.replacePlaceholdersInURL(field.target, params);

                    switch(field.data_key) {
                        case 'delete':
                            button = $('<button type="button" class="btn btn-danger">' + field.title + '</button>')
                            button.click(function() {
                                $.ajax({
                                    url: button_target,
                                    dataType: 'json',
                                    type: 'DELETE',
                                    success: function(response_data) {
                                        UI.showAlert('success', 'Data was successfully saved!');
                                        if(typeof response_data.db_changes !== 'undefined') {
                                            for(i in response_data.db_changes) {
                                                var store_data = response_data.db_changes[i];
                                                Stores.reloadStoresForModuleAndModel(store_data.module_name, store_data.model_name);
                                            }
                                        }
                                        
                                        $('#modalContainer').modal('hide');
                                        window.history.back();
                                    },
                                    error: function(response_data) {
                                        UI.showAlert('danger', 'Could not save data!');
                                        $('#modalContainer').modal('hide');
                                        window.history.back();
                                    }
                                });
                            });
                            break;

                        case 'cancel':
                            button = $('<button type="button" class="btn btn-default">' + field.title + '</button>')
                            button.click(function() {
                                $('#modalContainer').modal('hide');
                                window.history.back();
                            });
                            break;

                        default:
                            button = $('<button type="button" class="btn btn-default">' + field.title + '</button>')
                    }

                    footer_target.append(button);
            }
        }

        // Show the modal
        $('#modalContainer').modal('show');
    },
};