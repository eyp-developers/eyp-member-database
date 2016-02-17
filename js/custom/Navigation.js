/**
 * Navigation
 */

var Navigation = 
{
    setupListener: function() {
        window.onhashchange = function() {
            Navigation.navigateToURL(window.location.hash);
        };
    },

    navigateToHome : function() {
        $('#main').html('');
    },

    navigateToURL : function(url) {

        if(url.length == 0) {
            Navigation.navigateToHome();
            return;
        }

        // Make sure we are navigating locally
        if(url.indexOf('#') !== 0) {
            window.location = url;
        }

        // Extract the target from the URL
        var target = url.split('#')[1];

        if(target.length == 0) {
            Navigation.navigateToHome();
            return;
        }

        // Split the target into model and view
        target = target.split('/')
        if(target.length >= 3) {

            // Load the view config and handle it
            Server.ajax({
                dataType: 'json',
                url: '/backend/modules/' +  target[1] + '/views/' + target[2],
                success: function(response) {
                    // Apply view config
                    UI.applyViewConfig(response.data, target.slice(3));
                }
            });

        } else {
            if(target[1] === 'logout') {
                localStorage.removeItem('auth_token');
                window.location = '/';
            } else {
                console.error('URL "' + url + '" is not a valid target!');
            }
        }
    }
};