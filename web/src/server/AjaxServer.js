/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class AjaxServer extends AbstractServer {
    constructor(api, period) {
        super();
        this.api = api;
        this.period = period;
        this.interval = null;
        this.last = 0;
    }

    connect() {
        this.onConnect();
        this.interval = setInterval(() => {
            $.getJSON(this.api.poll, {last: this.last})
                .done((data) => {
                    if (!this.connected) {
                        this.onConnect();
                    }
                    this.last = data.last;

                    for (var i of data.queue) {
                        this.onData(i);
                    }
                })
                .fail((xhr, status) => {
                    if (this.connected) {
                        this.onError(status);
                    }
                    this.onDisconnect();
                });
        }, this.period);
    }

    sendData(data) {
        $.post(this.api.send, {data})
            .fail((xhr, status) => {
                this.onError(status);
            });
    }
}