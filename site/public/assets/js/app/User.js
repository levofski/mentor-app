App.User = Backbone.Model.extend({
    urlRoot: "/api/v1/users/",
    defaults: {
        id: '',
        first_name: '',
        last_name: '',
        email: '',
        irc_nick: '',
        github_handle: '',
        github_uid: '',
        twitter_handle: '',
        twitter_uid: '',
        skills: {},
        mentor_available: false,
        apprentice_available: false
    },
    initialize: function(options) {
        if (options.type == 'github') {
            var profile = window.Mentor.user.externalProfile;

            this.attributes.github_uid = profile.id,
            this.attributes.github_handle = profile.name,
            this.attributes.email = profile.email
        }
    }
});
