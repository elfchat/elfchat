/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class AbstractServer {
    constructor() {
        this.connected = false;

        // Consts from ElfChat\Server\Protocol:
        this.MESSAGE = 4;
        this.PRIVATE_MESSAGE = 5;
    }

    connect() {
        // You need to implement this.
    }

    sendToServer(data) {
        // You need to implement this.
    }

    send(text) {
        this.sendToServer(JSON.stringify([this.MESSAGE, text]));
    }

    sendPrivate(userId, text) {
        this.sendToServer(JSON.stringify([this.PRIVATE_MESSAGE, userId, text]));
    }

    onData(receive) {
        var json = JSON.parse(receive.data);
        var type = json[0];
        var data = json[1];

        switch (type) {
            case 0:
                this.onSynchronize(data);
                break;

            case 1:
                this.onUserJoin(data);
                break;

            case 2:
                this.onUserLeave(data);
                break;

            case 3:
                this.onUserUpdate(data);
                break;

            case 4:
                this.onMessage(data);
                break;

            case 6:
                this.onLog(data);
                break;

            default:
                throw new Error('Unknown message type received from server.');
        }
    }

    onConnect() {
        this.connected = true;
        $(window).trigger('connect');
    }

    onDisconnect() {
        if (this.connected) {
            this.connected = false;
            $(window).trigger('disconnect');
        }
    }

    onSynchronize(users) {
        $(window).trigger('synchronize', [users]);
    }

    onUserJoin(user) {
        $(window).trigger('user_join', user);
    }

    onUserLeave(user) {
        $(window).trigger('user_leave', user);
    }

    onUserUpdate(user) {
        $(window).trigger('user_update', user);
    }

    onMessage(message) {
        $(window).trigger('message', message);
    }

    onLog(message) {
        $(window).trigger('log', message);
    }

    onError(error) {
        console.error(error);
    }
}