/**
 * User Interface
 */
var UI =
{
    sidebar_config : null,

    applyViewConfig : function(config, params, dom_target) {

        // Get the main view if no target was defined
        if(typeof dom_target === 'undefined' || dom_target === null) {
            if(typeof config.container !== 'undefined'
                && config.container !== null
                && config.container !== '') {
                dom_target = $('#' + config.container);
            } else {
                var $main = $("#main");
                dom_target = $('<div class="row view"></div>');
                $main.html(dom_target);
            }
        }

        // Apply show_title config
        if(config.show_title == 0) {
            config.title = false;
        }

        // Handle the type of the view
        switch(config.type) {
            case 'table':
                var view_config = {
                    title: config.title,
                    datasource: Helpers.replacePlaceholdersInURL(config.datasource, params),
                    columns: config.fields,
                    dom_target: dom_target,
                    header_button_text: config.header_button_text,
                    header_button_icon: config.header_button_icon,
                    header_button_target: Helpers.replacePlaceholdersInURL(config.header_button_target, params)
                };
                UIComponents.table(view_config);
                break;

            case 'detail':
                var view_config = {
                    title: config.title,
                    datasource: Helpers.replacePlaceholdersInURL(config.datasource, params),
                    fields: config.fields,
                    dom_target: dom_target,
                    header_button_text: config.header_button_text,
                    header_button_icon: config.header_button_icon,
                     header_button_target: Helpers.replacePlaceholdersInURL(config.header_button_target, params)
                };
                UIComponents.detail(view_config);
                break;

            case 'form':
                var view_config = {
                    title: config.title,
                    datasource: Helpers.replacePlaceholdersInURL(config.datasource, params),
                    load_data: config.load_data,
                    fields: config.fields,
                    dom_target: dom_target,
                    header_button_text: config.header_button_text,
                    header_button_icon: config.header_button_icon,
                     header_button_target: Helpers.replacePlaceholdersInURL(config.header_button_target, params)
                };
                UIComponents.form(view_config);
                break;

            case 'combined':
                var view_config = {
                    title: config.title,
                    params: params,
                    fields: config.fields,
                    dom_target: dom_target,
                    header_button_text: config.header_button_text,
                    header_button_icon: config.header_button_icon,
                     header_button_target: Helpers.replacePlaceholdersInURL(config.header_button_target, params)
                };
                UIComponents.combined(view_config);
                break;

            case 'dialog':
                var view_config = {
                    title: config.title,
                    params: params,
                    fields: config.fields,
                    dom_target: dom_target
                };  
                UIComponents.dialog(view_config);
                break;

            default:
                console.error('Unsupported view type "' + config.type + '"');
        }
    },

    applySidebarConfig : function(sidebar_config) {
        UI.sidebar_config = sidebar_config;

        // Load sidebar items
        var sidebar_main_menu = $("#sidebar-main-menu");
        sidebar_main_menu.html('');

        // Build sidebar
        for(var menu_index in sidebar_config) {
            var menu_item = sidebar_config[menu_index];

            if(typeof menu_item.items !== 'undefined') {
                var dom_menu_item = UIComponents.sidebarDropdown(menu_item.title, menu_item.module, menu_item.icon, menu_item.items);
            } else {
                var dom_menu_item = UIComponents.sidebarItem(menu_item.title, menu_item.icon, menu_item.target);
            }

            sidebar_main_menu.append(dom_menu_item);
        }

        // Fix listeners for opening and closing
        $('#sidebar-main-menu > .dropdown').on('hide.bs.dropdown', function (e) {
            return false;
        });

        $('#sidebar-main-menu > .dropdown').on('click', function (e) {
            var menu = $(e.target).parent();
            
            if(menu.hasClass('open')) {
                menu.removeClass('open');
                return false;
            }
        });
    },

    applyUserMenuConfig : function(menu_item) {
        // Load sidebar items
        var $user_menu = $("#user-menu");
        $user_menu.html('');

        // Build user menu
        if(typeof menu_item.items !== 'undefined') {
            var dom_menu_item = UIComponents.userMenuDropdown(menu_item.title, menu_item.icon, menu_item.items);
        } else {
            var dom_menu_item = UIComponents.sidebarItem(menu_item.title, menu_item.icon, menu_item.target);
        }

        $user_menu.append(dom_menu_item);

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
        alert.slideDown(300).delay(3000).slideUp(300, function() { $(this).remove(); });

    },

    showLogin : function() {
        // Hide sidebar
        $('#sidebar').remove();
        $('#navbar').remove();
        $('#main').css('margin-left', 0);

        var $dom_main = $('#main');
        $dom_main.empty();

        // Create login form
        $dom_main.append([
            '<form id="form-signin">',
            '<h2 class="form-signin-heading">Please sign in</h2>',
            '<label for="inputUsername" class="sr-only">Username</label>',
            '<input type="text" id="inputUsername" class="form-control" placeholder="Username" required="" autofocus="">',
            '<label for="inputPassword" class="sr-only">Password</label>',
            '<input type="password" id="inputPassword" class="form-control" placeholder="Password" required="">',
            '<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>',
          '</form>'].join(''));

        // Add listener to form
        $('#form-signin').submit(function() {
            
            var data = {
                username: $('#inputUsername').val(),
                password: $('#inputPassword').val(),
            };

            $.ajax({
                dataType: 'json',
                url: 'backend/auth/login',
                type: 'POST',
                data: JSON.stringify(data),
                success: function(response) {
                    if(!response.data.auth_token) {
                        UI.showAlert('danger', "Could not log in. Please try again.");
                        return;
                    }

                    localStorage.setItem('auth_token', response.data.auth_token);
                    location.reload();
                },
                error: function() {
                    UI.showAlert('danger', "Could not log in. Please try again.");
                }
            });

            return false;
        });
    },

    showHome : function() {

        var $dom_view = $('<div class="row view"></div>');

        // Build sidebar
        for(var menu_index in UI.sidebar_config) {
            var menu_item = UI.sidebar_config[menu_index];

            $menu_box = $('<div class="col-xs-6 col-sm-4 col-md-3"></div></div>');

            var dom_icon = '';
            if(menu_item.icon) {
                dom_icon = '<span class="glyphicon glyphicon-' + menu_item.icon + '" aria-hidden="true"></span>';
            }
            var $box = $('<div class="box"><h4>' + dom_icon + ' ' + menu_item.title + '</h4></div>');
            
            var $list = $('<ul></ul>');
            for(list_index in menu_item.items) {
                var list_item = menu_item.items[list_index];

                var dom_icon = '';
                if(list_item.icon) {
                    dom_icon = '<span class="glyphicon glyphicon-' + list_item.icon + '" aria-hidden="true"></span>';
                }

                $list.append($('<li><a href="#' + list_item.target + '" class="menu-item">' + dom_icon + ' ' + list_item.title + '</a></li>'));
            }

            $box.append($list);
            $menu_box.append($box);
            $dom_view.append($menu_box);
        }

        $('#main').html($dom_view);
    }
};