/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function Notify() {
    this.error = function (error) {
        return $.notification({
            title: tr('Error'),
            content: error,
            icon: 'icon-info-sign',
            error: true
        });
    };

    this.alert = function (text) {
        return $.notification({
            title: tr('Info'),
            content: text,
            icon: 'icon-html5'
        });
    };

    var connecting = null;
    this.connecting = {
        start: function () {
            if (connecting != null) {
                connecting.hide();
            }
            return connecting = $.notification({
                content: tr('Connecting'),
                icon: 'icon-spinner icon-spin'
            });
        },
        stop: function () {
            return connecting.remove();
        }
    }
}