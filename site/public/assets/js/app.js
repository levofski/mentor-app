window.App = {};
window.Mentor = {}; // this is our global for storing important things

App.init = function() {
    window.Mentor.apiUrl = 'http://mentorapp.dev:8080/api/v1';
    window.Mentor.user = null;

    $.ajaxSetup({
        'dataType': 'json'
    });

    Backbone.emulateHTTP = true;
    
    App.Router = Backbone.Router.extend({

        routes: {
            "": "home",
            "login": "login",
            "profile/:id": "showProfile",
            "search": "search",
            "account": "account",
        },

        home: function() {

        },
        login: function() {
			/**
            var user = new App.User({'id': '003ed1ea5a'});
            var profile = new App.UserProfileView({model: user});
            */
			var login = new App.LoginView();
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

