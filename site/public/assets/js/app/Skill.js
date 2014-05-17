App.Skill = Backbone.Model.extend({
    urlRoot: '/api/v1/skill/',
    defaults: {
        id: '',
        name: '',
        approved: false
    },
    initialize: function() {
        this.fetch();
    }
});
