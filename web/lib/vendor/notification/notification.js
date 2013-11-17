(function ($) {
    var msie = /msie/.test(navigator.userAgent.toLowerCase());

    $.notification = function (settings) {
        var container, notification, close, hide, image, right, left, inner;

        settings = $.extend({
            title: void 0,
            content: void 0,
            timeout: 0,
            img: void 0,
            icon: void 0,
            fill: false,
            click: void 0,
            error: false
        }, settings);

        container = $("#notifications");
        if (!container.length) {
            container = $("<div>", { id: "notifications" }).appendTo($("body"));
        }

        notification = $("<div>");
        notification.addClass("notification animated fadeInLeftMiddle fast");

        if (settings.error == true) {
            notification.addClass("error");
        }

        if ($("#notifications .notification").length > 0) {
            notification.addClass("more");
        } else {
            container.addClass("animated flipInX").delay(1000).queue(function () {
                container.removeClass("animated flipInX");
                container.clearQueue();
            });
        }

        close = $("<div>", {
            click: function () {
                $this = $(this).parent();
                if ($this.is(':last-child')) {
                    $this.remove();
                    $('#notifications .notification:last-child').removeClass("more");
                } else {
                    $this.remove();
                }
            }
        });

        close.addClass("hide");

        left = $("<div class='left'>");
        right = $("<div class='right'>");

        if (settings.title != void 0) {
            var htmlTitle = "<h2>" + settings.title + "</h2>";
            notification.addClass("big");
        } else {
            var htmlTitle = "";
        }

        if (settings.content != void 0) {
            var htmlContent = settings.content;
        } else {
            var htmlContent = "";
        }

        inner = $("<div>", { html: htmlTitle + htmlContent });
        inner.addClass("inner");

        inner.appendTo(right);

        if (settings.img != void 0) {
            image = $("<div>", {
                style: "background-image: url('" + settings.img + "')"
            });

            image.addClass("img");
            image.appendTo(left);

            if (settings.fill == true) {
                image.addClass("fill");
            }
        } else if (settings.icon != void 0) {
            icon = $("<i></i>").addClass(settings.icon);
            icon.appendTo(left);
        }

        left.appendTo(notification);
        right.appendTo(notification);

        close.appendTo(notification);

        notification.hover(
            function () {
                close.show();
            },
            function () {
                close.hide();
            }
        );

        notification.prependTo(container);
        notification.show();

        if (settings.timeout) {
            setTimeout(function () {
                var prev = notification.prev();
                if (prev.hasClass("more")) {
                    if (prev.is(":first-child") || notification.is(":last-child")) {
                        prev.removeClass("more");
                    }
                }
                notification.remove();
            }, settings.timeout)
        }

        if (settings.click != void 0) {
            notification.addClass("click");
            notification.bind("click", function (event) {
                var target = $(event.target);
                if (!target.is(".hide")) {
                    settings.click.call(this)
                }
            })
        }

        return notification;
    }
})(jQuery);