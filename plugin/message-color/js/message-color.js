var messageColor = localStorage.getItem('message-color') || '';

$(function () {
    var textarea = $('footer textarea');
    textarea.css('color', messageColor);

    $('#colorpicker').farbtastic(function (color) {
        localStorage.setItem('message-color', color);
        messageColor = color;
        textarea.css('color', color);
    });

    $.farbtastic('#colorpicker').setColor(messageColor);

    $(window).on('send', function (event, message) {
        message.color = messageColor;
    });
});