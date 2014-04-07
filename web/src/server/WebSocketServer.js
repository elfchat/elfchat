/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class WebSocketServer extends AbstractServer {
    constructor(server, port) {
        super();
        this.socket = null;
        this.server = server;
        this.port = port;
        this.reconnect = null;
    }

    connect() {
        this.socket = new WebSocket('ws://' + this.server + ':' + this.port);

        this.socket.onopen = () => {
            this.onConnect();
            clearInterval(this.reconnect);
        };

        this.socket.onclose = (event) => {
            if (event.wasClean) {
                this.onDisconnect();
            } else {
                this.onDisconnect();

                clearInterval(this.reconnect);
                this.reconnect = setInterval(() => {
                    this.connect();
                }, 1000);
            }
        };

        this.socket.onmessage = (receive) => {
            this.onData(receive);
        };

        this.onerror = (error) => {
            this.onError(error.message);
        };
    }

    send(text) {
        this.socket.send(text);
    }

    sendPrivate(userId, text) {
    }
}