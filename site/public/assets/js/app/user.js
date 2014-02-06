App.User = Backbone.Model.extend({
    urlRoot: "user/",
    defaults: {
        id: '',
        first_name: '',
        last_name: '',
        email_address: '',
        irc_handle: '',
        github: '',
        twitter_handle: '',
        skills: {},
        mentor_available: false,
        apprentice_available: false,
    }
});
