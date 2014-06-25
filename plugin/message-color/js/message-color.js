var messageColor = '#000000';

$(function () {
    $('#colorpicker').farbtastic(function (color) {
        messageColor = color;
    });

    $(window).on('send', function (event, data) {
        data.message = messageColor + '#' + data.message;
    });
});

container.extend('app', function (app) {
    app.filters.push(new MessageColorFilter());
    return app;
});

function MessageColorFilter()
{
    this.filter = function (html) {
        var color = '#000000';
        html = html.replace(/^([#0-9a-z]{7})#(.*?)/ig, function (m, p1, p2) {
            color = p1;
            return p2;
        });
        return '<span style="color:' + color + '">' + html + '</span>';
    };
}