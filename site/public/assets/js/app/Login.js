App.Login = Backbone.Model.extend({
    defaults: {
        type: null,
        status: 0
    },
    initialize: function(options) {
        this.fetchThirdPartyUserProfile(options.type);
    },
    fetchThirdPartyUserProfile: function(type) {
        var self = this,
            requestUrl = window.Mentor.apiUrl + '/' + type + '/profile';

        $.ajax({
            url: requestUrl,
            dataType: "json",
            success: function(data){
                if (_.isObject(data)) {
                    window.Mentor.user.externalProfile = data;
                    self.validateUser(data.id, type);
                }
            }
        });
    },
    validateUser: function(id, type) {
        var self = this,
            requestUrl = window.Mentor.apiUrl + '/users/' + id + '?third_party=' + type;

        $.ajax({
            url: requestUrl,
            dataType: "json",
            success: function(data){
                if (_.isObject(data)) {
                    // do other things
                    console.log(data);
                }
            },
            error: function() {
                // received 404 response which means this is a new user
                Backbone.history.navigate('/newUser/' + type, true);
            }
        });
    }
});