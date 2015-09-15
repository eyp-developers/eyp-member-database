/**
 * Installer
 */
 var Installer = 
 {
    init : function() {
        // Hide sidebar
        $('#sidebar').remove();
        $('body').css('padding-left', 0);

        var $dom_main = $('#main');
        
        var $dom_installer = $('<div class="installer"></div>');

        Installer.step_1 = function() {
            $dom_installer.empty();
            $dom_installer.append($('<h1>Welcome!</h1>'));
            $dom_installer.append($('<p>Please enter your database configuration in the file <span class="mono">/backend/_Core/Config.php</span><p>'));
            $dom_installer.append($('<p>Click below to check your configuration:<p>'));

            var $btn_check = $('<button type="button" class="btn btn-primary btn-lg">Check database connection</button>');

            $btn_check.click(function() {
                $.ajax({
                    url: '/installer/installer.php',
                    dataType: 'json',
                    type: 'GET',
                    success: function(response_data) {
                        if(typeof response_data.success === 'undefined' || response_data.success === true) {
                            Installer.step_2();
                        } else {
                            message = response_data.message;
                            UI.showAlert('danger', message);
                        }
                        
                        $('#modalContainer').modal('hide');
                    },
                    error: function(response_data) {
                        UI.showAlert('danger', 'Could not start the installer!');
                        $('#modalContainer').modal('hide');
                    }
                });
            });

            $dom_installer.append($btn_check);
        };

        Installer.step_2 = function() {
            $dom_installer.empty();
            $dom_installer.append($('<h1>Congratulations!</h1>'));
            $dom_installer.append($('<p>Your database is set up properly.<p>'));
            $dom_installer.append($('<p>Click below to finish up the installation:<p>'));

            var $btn_install = $('<button type="button" class="btn btn-primary btn-lg">Finish installation</button>');

            $btn_install.click(function() {
                $.ajax({
                    url: '/backend/modules/install/settings',
                    dataType: 'json',
                    type: 'POST',
                    success: function(response_data) {
                        if(typeof response_data.success === 'undefined' || response_data.success === true) {
                            location.reload()
                        } else {
                            message = 'Could not finish installation!';
                            if(response_data.message) {
                                message = response_data.message;
                            }

                            UI.showAlert('danger', message);
                        }
                        
                        $('#modalContainer').modal('hide');
                        location.reload();
                    },
                    error: function(response_data) {
                        UI.showAlert('danger', 'Could not finish installation!');
                        $('#modalContainer').modal('hide');
                    }
                });
            });

            $dom_installer.append($btn_install);
            $dom_installer.append($('<p class="small"><span class="glyphicon glyphicon-flash" aria-hidden="true"></span> You can install additional modules under <span class="mono">Settings</span> - <span class="mono">Module Management</span>.<p>'));
            $dom_installer.append($('<p class="small"><span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span> Please remove the <span class="mono">installer</span> folder from the application directory for security reasons!<p>'));
        }

        $dom_main.append($dom_installer);

        // Start the installation
        Installer.step_1();
    }
}