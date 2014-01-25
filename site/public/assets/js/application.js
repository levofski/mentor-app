var User = Backbone.Model.extend({
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

var Skill = Backbone.Model.extend({
    defaults: {
        id: '',
        name: '',
        approved: false,
    }
});

var Partnership = Backbone.Model.extend({
    defaults: {
        id: '',
        mentor: '',
        apprentice: ''
    }
});
