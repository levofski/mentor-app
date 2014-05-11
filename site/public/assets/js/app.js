window.App = {};

App.init = function() {
    var UserProfile = null;

    $.ajaxSetup({
        'dataType': 'json'
    });

    Backbone.emulateHTTP = true;
    
    App.Router = Backbone.Router.extend({

        routes: {
            "": "login",
            "profile/:id": "showProfile",
            "search": "search",
            "account": "account",
        },

        login: function() {
			/**
            var user = new App.User({'id': '003ed1ea5a'});
            var profile = new App.UserProfileView({model: user});
            */
			var login = new App.Login();
        },

       showProfile: function(id) {
            var user = new App.User({'id': id});
            var profile = new App.UserProfileView({model: user});
       },

       search: function() {
           alert('search');
       },

       account: function() {
           var user = new App.User({'id': ''});
           var account = new App.AccountView({model: user});
       }

    });
    var router = new App.Router();
    Backbone.history.start({pushState: true});
};

