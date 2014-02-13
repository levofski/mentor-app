App.UserProfileView = Backbone.View.extend({
    tagname: 'div',

    template: _.template($("#profile-view").html()),

    el: "#application-content",

    events: {
        'click .docblock_notation': 'show'
    },

    model: new App.User(),

    initialize: function() {
        this.render();
    },
    
    render: function() {
       this.model.fetch();
       this.$el.html(this.template(this.model.toJSON()));
       return this;
    },

    show: function() {
        alert('hello');
    }
});
