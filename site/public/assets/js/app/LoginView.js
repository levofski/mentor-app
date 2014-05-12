App.LoginView = Backbone.View.extend({
    tagname: 'div',

    template: _.template($("#login-view").html()),

    el: "#application-content",

    events: {
        'click button': 'doLogin'
    },

    initialize: function () {
        this.render();
    },
    render: function () {
        this.$el.html(this.template());
        return this;
    },
    doLogin: function (ev) {
        var type = $(ev.target).data('type');
        var pageUrl = window.Mentor.apiUrl + '/oauth/' + type;

        $.ajax({
            url: pageUrl,
            dataType: "json",
            success: function(data){
                if (_.isObject(data) && !_.isEmpty(data.redirect)) {
                    window.location = data.redirect;
                }
            }
        });
    }
});
