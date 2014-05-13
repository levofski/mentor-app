window.App = {};
window.Mentor = {}; // this is our global for storing important things

App.init = function() {
    window.Mentor.apiUrl = 'http://mentorapp.dev:8080/api/v1';
    window.Mentor.user = { status: 0 };

    $.ajaxSetup({
        'dataType': 'json'
    });

    Backbone.emulateHTTP = true;
    
    App.Router = Backbone.Router.extend({
        routes: {
            "": "home",
            "login": "login",
            "doLogin/:type": "doLogin",
            "newUser/:type": "newUser",
            "profile/:id": "showProfile",
            "search": "search",
            "account": "account"
        },
        home: function() {},
        login: function() {
            /**
            var user = new App.User({'id': '003ed1ea5a'});
            var profile = new App.UserProfileView({model: user});
            */
            new App.LoginView();
        },
        newUser: function(type) {
            var user = new App.User({type: type});
            new App.AccountView({model: user});
        },
        doLogin: function(type) {
            new App.Login({type: type});
        },
        showProfile: function(id) {
            var user = new App.User({'id': id});
            new App.UserProfileView({model: user});
        },
        search: function() {
           alert('search');
        },
        account: function() {
           var user = new App.User({'id': ''});
           new App.AccountView({model: user});
        }
    });
    var router = new App.Router();
    Backbone.history.start({pushState: true});
};