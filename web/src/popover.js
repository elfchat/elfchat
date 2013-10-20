/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var popovers = {};

class Popover {
    constructor(id, button = null, box = '.box') {
        var _this = this;

        this.id = id;
        this.box = $(box);
        this.margin = 10;
        this.onTop = 'top';
        this.onBottom = 'bottom';
        this.onLeft = 'left';
        this.onRight = 'right';
        this.autohide = false;

        if (button !== null) {
            this.on(button);
        }

        $(window).resize(function () {
            return _this.reposition();
        });

        $('body').mouseup((event) => {
            if (_this.autohide && _this.getPopover().has(event.target).length === 0) {
                _this.hide();
            }
        });
    }

    static create(id, button) {
        if (popovers[id]) {
            return popovers[id].on(button);
        } else {
            return popovers[id] = new Popover(id, button);
        }
    }

    static get(id) {
        return popovers[id];
    }

    on(button) {
        var _this = this;
        this.button = $(button);
        this.button.mousedown((event) => {
            _this.autohide = false;
        });
        return this;
    }

    reposition() {
        var arrow, arrowPosition, boxSize, buttonOffset, buttonSize, offset, over, popover, popoverPosition, popoverSize;

        arrow = this.getArrow();
        popover = this.getPopover();
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
            width: popover.outerWidth(),
            height: popover.outerHeight()
        };
        if (popover.hasClass(this.onLeft) || popover.hasClass(this.onRight)) {
            if (popover.hasClass(this.onLeft)) {
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
            if (popoverPosition.top + popoverSize.height > boxSize.height || popover.hasClass(this.onTop)) {
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
        popover.css(popoverPosition);
        arrow.css(arrowPosition);
    }

    show() {
        this.reposition();
        this.getPopover().show();
        return this.autohide = true;
    }

    hide() {
        return this.getPopover().hide();
    }

    toggle() {
        this.reposition();
        this.getPopover().toggle();
        return this.autohide = true;
    }

    getPopover() {
        return $('#' + this.id);
    }

    getArrow() {
        return this.getPopover().find('.arrow');
    }
}

