/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Scroll {
    constructor(div) {
        var _this = this;

        this.div = div;
        this.able = true;
        this.scrolling = 0;
        this.div.on('scroll', function(e) {
            if (_this.scrolling === 0) {
                return _this.able = _this.div.scrollTop() + _this.div.outerHeight() + 10 > _this.div[0].scrollHeight;
            }
        });
    }

    down(){
        var _this = this;

        if (this.able) {
            this.scrolling += 1;
            return this.div.scrollTo('100%', 300, {
                onAfter: function() {
                    return _this.scrolling -= 1;
                }
            });
        }
    }

    instantlyDown() {
        var height = this.div[0].scrollHeight;
        return this.div.scrollTop(height);
    }
}