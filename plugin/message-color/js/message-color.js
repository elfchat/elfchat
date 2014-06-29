var messageColor = '#000000';

$(function () {
    var textarea = $('footer textarea');
    $('#colorpicker').farbtastic(function (color) {
        messageColor = color;
        textarea.css('color', color);
    });

    $(window).on('send', function (event, message) {
        message.color = messageColor;
    });
});