App.AccountView = Backbone.View.extend({
    tagname: 'div',

    template: _.template($("#account").html()),

    el: "#application-content",

    events: {},

    initialize: function() {
        this.render();
    },

    render: function() {
        this.$el.html(this.template(this.model.toJSON()));
        return this;
    }
});
