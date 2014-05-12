App.Skill = Backbone.Model.extend({
    urlRoot: 'skill/',
    defaults: {
        id: '',
        name: '',
        approved: false,
    }
});
