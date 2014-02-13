window.App = {};

App.init = function() {
    var UserProfile = null;

    $.ajaxSetup({
        'dataType': 'json'
    });

    Backbone.emulateHTTP = true;
    
    UserProfile = new App.UserProfileView({
        el: $("#application-content"),
    });
};

