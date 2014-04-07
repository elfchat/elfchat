/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Filter {

    filter(html) {
        return this.explode(html, {
            tag: $.proxy(this.tag, this),
            text: $.proxy(this.text, this)
        });
    }

    explode(html, map) {
        var array, i, length, part, tag, text, _i, _ref, _ref1;
        if (map == null) {
            map = null;
        }
        array = [''];
        length = html.length;
        part = 0;
        for (i = _i = 0; 0 <= length ? _i <= length : _i >= length; i = 0 <= length ? ++_i : --_i) {
            if (html.charAt(i) === '<') {
                array.push('');
                part++;
            }
            array[part] += html.charAt(i);
            if (html.charAt(i) === '>') {
                array.push('');
                part++;
            }
        }
        tag = (_ref = map.tag) != null ? _ref : function (value) {
            return value;
        };
        text = (_ref1 = map.text) != null ? _ref1 : function (value) {
            return value;
        };
        array = array.map(function (value, n) {
            if (value === '') {
                return value;
            }
            if (n % 2 === 1) {
                return tag(value);
            } else {
                return text(value);
            }
        });
        return array.join('');
    }
}


class BBCodeFilter extends Filter {
    text(text) {
        // " is &quot;
        // / is &#x2f;

        // Background
        text = text.replace(/\[bg=([#0-9a-z]{1,20})\]((?:.(?!\[bg))*)\[\/bg\]/ig, '<span style="background-color:$1;">$2</span>');

        // Color
        text = text.replace(/\[color=([#0-9a-z]{1,20})\]((?:.(?!\[color))*)\[\/color\]/ig, '<span style="color:$1;">$2</span>');

        // Bold, italic, ext.
        text = text.replace(/\[b\]((?:.(?!\[b\]))*)\[\/b\]/ig, '<b>$1</b>');
        text = text.replace(/\[i\]((?:.(?!\[i\]))*)\[\/i\]/ig, '<i>$1</i>');
        text = text.replace(/\[s\]((?:.(?!\[s\]))*)\[\/s\]/ig, '<s>$1</s>');

        // Marquee
        text = text.replace(/\[m\]((?:.(?!\[s\]))*)\[\/m\]/ig, '<marquee>$1</marquee>');

        // Blockquote
        text = text.replace(/\[quote([^\]]*)\]((?:.(?!\[quote))*)\[\/quote\]/ig, function (m, p1, p2) {
            var info, msg, name, quote, time;
            info = '';
            quote = '';
            msg = p1.match(/msg=&quot;([0-9]*)&quot;/);
            if (msg !== null && msg[1] !== '') {
                quote = ' ref="' + msg[1] + '"';
            }
            time = p1.match(/time=&quot;([\.:0-9]*)&quot;/);
            if (time !== null && time[1] !== '') {
                info += '<i>' + time[1] + '</i> ';
            }
            name = p1.match(/name=&quot;(.*)&quot;/);
            if (name !== null && name[1] !== '') {
                info += name[1];
            }
            if (info !== '') {
                info = '&copy; ' + info + ': ';
            }
            return '<blockquote' + quote + '>' + info + p2 + '</blockquote>';
        });

        return text;
    }
}


class RestrictionFilter extends Filter {
    constructor() {
        this.maximumLengthOfWords = 100;
        this.maximumNumberOfLines = 20;
    }

    filter(html) {
        var linesCount,
            _this = this;

        // loo...oong words
        html = this.explode(html, {
            text: function (text) {
                return text.replace(new RegExp('[^\\s]{' + _this.maximumLengthOfWords + ',}', 'g'), function (all) {
                    return all.substr(0, 20) + '...' + all.substr(all.length - 20, all.length);
                });
            }
        });

        // a lot of spaces
        html = html.replace(/([\s]{100,})/g, function (all) {
            return all.substr(0, 100);
        });

        // empty lines:
        html = html.replace(/(\n){3,}/g, '\n\n');

        // maximum number of lines:
        linesCount = 0;
        html = html.replace(/[\n\r\t]/g, function (all) {
            if (++linesCount < _this.maximumNumberOfLines) {
                return '\n';
            } else {
                return ' ';
            }
        });

        return html;
    }
}


class UriFilter extends Filter {
    constructor() {
        var _ref, _ref1, _ref2;

        this.imageable = (_ref1 = init.imageable) != null ? _ref1 : true;
        this.imageCount = 0;
        this.maxImages = (_ref2 = init.maxImages) != null ? _ref2 : 3;
        this.regex = /(https?):\/\/((?:[a-z0-9.-]|%[0-9A-F]{2}){3,})(?::(\d+))?((?:\/(?:[a-z0-9-._~!$&'()*+,;=:@]|%[0-9A-F]{2})*)*)(?:\?((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9A-F]{2})*))?(?:#((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9A-F]{2})*))?/ig;
    }

    text(text) {
        this.images = 0;
        return text = text.replace(this.regex, $.proxy(this.callback, this));
    }

    callback(uri, p1, p2, p3, p4, p5, p6, p7, p8, p9) {
        var ext, id, img, text, _ref,
            _this = this;

        text = uri;
        ext = uri.match(/\.([a-z0-9]+)$/i);
        if (((_ref = ext != null ? ext[1] : void 0) === 'jpg' || _ref === 'jpeg' || _ref === 'png' || _ref === 'gif') && this.imageable && this.images++ < this.maxImages) {
            this.imageCount += 1;
            id = 'external-img-' + this.imageCount;
            text = "<img class=\"external\" id=\"" + id + "\" src=\"" + uri + "\">";
            img = new Image();
            img.src = uri;
            img.onload = function () {
                return (function (id, uri) {
                    var height;

                    height = $('#' + id).height();

                    window.scroll.down();
                })(id, uri);
            };
        }
        return "<a href=\"" + uri + "\" target=\"_blank\">" + text + "</a>";
    }
}

class EmotionFilter extends Filter {
    constructor(list) {
        this.list = list;
        this.max = 20;
    }

    text(text) {
        var _this = this;

        var count = 0;
        text = text.replace(/&apos;/g, "'");
        for (var row of this.list) {
            var regexp = row[0];
            var src = row[1];
            text = text.replace(regexp, (str) => {
                count++;
                if (count > _this.max) {
                    return str;
                } else {
                    return new EmotionImageView(src).render();
                }
            });
        }
        text = text.replace(/'/g, '&apos;');
        return text;
    }
}