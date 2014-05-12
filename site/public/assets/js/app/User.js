App.User = Backbone.Model.extend({
    urlRoot: "/api/v1/users/",
    defaults: {
        id: '',
        first_name: '',
        last_name: '',
        email: '',
        irc_nick: '',
        github_handle: '',
        twitter_handle: '',
        skills: {},
        mentor_available: false,
        apprentice_available: false,
    }
});
