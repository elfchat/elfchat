/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var popovers = {};

class Popover {
    constructor(popover, button = null, box = '.box') {
        var _this = this;

        this.popover = popover;
        this.box = $(box);
        this.margin = 10;
        this.onTop = 'top';
        this.onBottom = 'bottom';
        this.onLeft = 'left';
        this.onRight = 'right';
        this.arrow = this.popover.find('.arrow');
        this.autohide = false;

        if(button !== null) {
            this.on(button);
        }

        $(window).resize(function () {
            return _this.reposition();
        });

        $('body').mouseup((event) => {
            if (_this.autohide && _this.popover.has(event.target).length === 0) {
                _this.hide();
            }
        });
    }

    static create(id, button) {
        if (popovers[id]) {
            return popovers[id].on(button);
        } else {
            return popovers[id] = new Popover($('#' + id), button);
        }
    }

    on(button) {
        var _this = this;
        this.button = button;
        this.button.mousedown((event) => {
            _this.autohide = false;
        });
        return this;
    }

    reposition() {
        var arrowPosition, boxSize, buttonOffset, buttonSize, offset, over, popoverPosition, popoverSize;

        boxSize = {
            width: this.box.width(),
            height: this.box.height()
        };
        buttonOffset = this.button.offset();
        buttonSize = {
            width: this.button.outerWidth(),
            height: this.button.outerHeight()
        };
        popoverSize = {
            width: this.popover.outerWidth(),
            height: this.popover.outerHeight()
        };
        if (this.popover.hasClass(this.onLeft) || this.popover.hasClass(this.onRight)) {
            if (this.popover.hasClass(this.onLeft)) {
                popoverPosition = {
                    top: buttonOffset.top - (popoverSize.height / 2) + (buttonSize.height / 2),
                    left: buttonOffset.left - popoverSize.width
                };
            } else {
                popoverPosition = {
                    top: buttonOffset.top - (popoverSize.height / 2) + (buttonSize.height / 2),
                    left: buttonOffset.left + buttonSize.width
                };
            }
            arrowPosition = {
                top: popoverSize.height / 2
            };
            if ((over = popoverPosition.top + popoverSize.height) > boxSize.height) {
                offset = over - boxSize.height + this.margin;
                popoverPosition.top -= offset;
                arrowPosition.top += offset;
            }
            if ((over = popoverPosition.top) < 0) {
                offset = -over + this.margin;
                popoverPosition.top += offset;
                arrowPosition.top -= offset;
            }
        } else {
            popoverPosition = {
                top: buttonSize.height + buttonOffset.top,
                left: buttonOffset.left - (popoverSize.width / 2) + (buttonSize.width / 2)
            };
            arrowPosition = {
                left: popoverSize.width / 2
            };
            if (popoverPosition.top + popoverSize.height > boxSize.height || this.popover.hasClass(this.onTop)) {
                popoverPosition.top = buttonOffset.top - popoverSize.height;
            }
            if ((over = popoverPosition.left + popoverSize.width) > boxSize.width) {
                offset = over - boxSize.width + this.margin;
                popoverPosition.left -= offset;
                arrowPosition.left += offset;
            }
            if ((over = popoverPosition.left) < 0) {
                offset = -over + this.margin;
                popoverPosition.left += offset;
                arrowPosition.left -= offset;
            }
        }
        this.popover.css(popoverPosition);
        return this.arrow.css(arrowPosition);
    }

    show() {
        this.reposition();
        this.popover.show();
        return this.autohide = true;
    }

    hide() {
        return this.popover.hide();
    }

    toggle() {
        this.reposition();
        this.popover.toggle();
        return this.autohide = true;
    }
}

