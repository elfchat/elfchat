var messageColor = '#000000';

$(function () {
    $('#colorpicker').farbtastic(function (color) {
        messageColor = color;
    });

    $(window).on('send', function (event, message) {
        message.color = messageColor;
    });
});