window.App = {};

App.init = function() {
    var UserSearch = null;

    $.ajaxSetup({
        'dataType': 'json'
    });

    Backbone.emulateHTTP = true;

    UserSearch = new App.UserSearchView({
        el: $('#usersearch')
    });
};
