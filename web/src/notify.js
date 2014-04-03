/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function Notify() {
    this.error = function (error) {
//        return $.notification({
//            title: tr('Error'),
//            content: error,
//            icon: 'fa fa-info',
//            error: true
//        });
    };

    this.alert = function (text) {
//        return $.notification({
//            title: tr('Info'),
//            content: text,
//            icon: 'fa fa-info'
//        });
    };

    var connecting = null;
    this.connecting = {
        start: function () {
//            if (connecting != null) {
//                connecting.hide();
//            }
//            return connecting = $.notification({
//                content: tr('Connecting'),
//                icon: 'fa fa-spinner fa-spin'
//            });
        },
        stop: function () {
//            return connecting.remove();
        }
    }
}