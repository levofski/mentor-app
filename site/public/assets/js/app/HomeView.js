Backbone.View.extend({
    tagname: 'div',

    template: _.template($("#home-view").html()),

    el: "#application-content",

    events: {},

    initialize: function () {
        this.render();
    },
    render: function () {
        this.$el.html(this.template());
        return this;
    }
});