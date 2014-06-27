/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class AbstractServer {
    constructor() {
        this.connected = false;

        // From ElfChat\Server\Protocol:

        this.SYNCHRONIZE = 0;

        this.USER_JOIN = 1;

        this.USER_LEAVE = 2;

        this.USER_UPDATE = 3;

        this.MESSAGE = 4;

        this.PRIVATE_MESSAGE = 5;

        this.LOG = 6;
    }

    connect() {
        // You need to implement this.
    }

    sendData() {
        // You need to implement this.
    }

    send(message) {
        this.sendData(JSON.stringify([this.MESSAGE, message]));
    }

    sendPrivate(userId, message) {
        this.sendData(JSON.stringify([this.PRIVATE_MESSAGE, userId, message]));
    }

    onData(json) {
        var type = json[0];
        var data = json[1];

        switch (type) {
            case this.SYNCHRONIZE:
                this.onSynchronize(data);
                break;

            case this.USER_JOIN:
                this.onUserJoin(data);
                break;

            case this.USER_LEAVE:
                this.onUserLeave(data);
                break;

            case this.USER_UPDATE:
                this.onUserUpdate(data);
                break;

            case this.MESSAGE:
                this.onMessage(data);
                break;

            case this.LOG:
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