App.UserProfileView = Backbone.View.extend({
    tagname: 'div',

    template: _.template($("#profile-view").html()),

    el: "#application-content",

    events: {
        'click .docblock_notation': 'show'
    },

    initialize: function() {
        this.model.bind('change', _.bind(this.render, this));
        this.render();
    },
    
    render: function() {
       this.model.fetch();
       this.$el.html(this.template(this.model.toJSON()));
       return this;
    },

    show: function() {
       console.log(this.model);
    }
});
