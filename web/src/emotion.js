/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Emotion {
    constructor() {
        this.dom = {
            popover: $('#emotions'),
            content: $('#emotions .content'),
            button: $('button[data-popover="emotions"]'),
            textarea: $('footer textarea')
        };
        this.currentTab = 0;
        if (EmotionCatalog) {
            this.tabs = new EmotionTabs(EmotionCatalog);
            this.bind();
        }
    }

    onButtonClick(event) {
        this.render();
    }

    render() {
        this.dom.content.html(this.tabs.render());

        var wait = [];
        this.dom.content.find('img').each(function (index, img) {
            var deferred = $.Deferred();
            img.onload = function () {
                return deferred.resolve();
            };
            wait.push(deferred);
        });

        $.when.apply($, wait).done(function () {
            Popover.get('emotions').reposition();
        });
    }


    bind() {
        var _this = this;

        this.dom.button.one('click', function () {
            _this.render();
        });

        _this.dom.button.click(function () {
            _this.dom.textarea.focus();
        });

        _this.dom.popover.on('click', function () {
            return _this.dom.textarea.focus();
        });

        _this.dom.popover.on('click', '.left', function () {
            _this.currentTab--;
            if (_this.currentTab < 0) {
                _this.currentTab = _this.tabs.max - 1;
            }
            return _this.dom.content.scrollTo(_this.getTab(_this.currentTab), 300);
        });

        _this.dom.popover.on('click', '.right', function () {
            _this.currentTab++;
            if (_this.currentTab >= _this.tabs.max) {
                _this.currentTab = 0;
            }
            return _this.dom.content.scrollTo(_this.getTab(_this.currentTab), 300);
        });

        _this.dom.popover.on('click', '[data-emotion]', function () {
            var emotion = $(this).attr('data-emotion');
            return _this.dom.textarea.insertAtCaret(" " + emotion + " ");
        });
    }

    getTab(i) {
        return this.dom.content.find(".tab:eq(" + i + ")");
    }
}

class EmotionTabs {
    constructor(catalog) {
        this.pertab = 28;
        this.catalog = catalog;
        this.n = 0;
        this.emotions = [];
        this.max = 0;
    }

    render() {
        var html = '';

        for (var title in this.catalog) if (this.catalog.hasOwnProperty(title)) {
            var tab = this.catalog[title];
            for (var emotion of tab) {
                this.emotions.push(emotion);

                if (this.n >= this.pertab - 1) {
                    html += this.renderTab(title, this.emotions);
                } else {
                    this.n++;
                }
            }
            if (this.n !== 0) {
                html += this.renderTab(title, this.emotions);
            }
        }

        return html;
    }


    renderTab(title, emotions) {
        this.n = 0;
        this.emotions = [];
        this.max++;
        return new EmotionTabView(tr(title), emotions).render();
    }
}