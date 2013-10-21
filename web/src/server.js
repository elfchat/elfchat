/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Server {
    constructor(server, namespace) {
        this.server = server;
        this.namespace = namespace;
    }

    send(text, room = 'main') {
        if (text === '') {
            return;
        }

        $.post(window.config.api.send, {text, room}, null, 'json').done((res) => {
            if (res.error !== false) {
                $(window).trigger('error', res.error);
            }
        });
    }

    connect() {
        this.socket = io.connect(this.server + '/' + this.namespace, {
            query: 'namespace=' + this.namespace
        });
        this.bindSocket();
        this.socketLog();
    }

    login(auth) {
        this.socket.emit('login', auth);
    }

    join(room) {
        this.socket.emit('join', room);
    }

    bindSocket() {
        this.socket.on('connect', () => {
            $(window).trigger('connect');
        });

        this.socket.on('disconnect', () => {
            $(window).trigger('disconnect');
        });

        this.socket.on('reconnect', () => {
            $(window).trigger('reconnect');
        });

        this.socket.on('synchronize', (users) => {
            $(window).trigger('synchronize', users);
        });

        this.socket.on('login_success', () => {
            $(window).trigger('login_success');
        });

        this.socket.on('user_join', (user) => {
            $(window).trigger('user_join', user);
        });

        this.socket.on('user_leave', (user) => {
            $(window).trigger('user_leave', user);
        });

        this.socket.on('message', (message) => {
            $(window).trigger('message', message);
        });

        this.socket.on('error', (error) => {
            $(window).trigger('error', error);
        });
    }

    socketLog() {
        var socket = this.socket;
        socket.on('connect', function () {
            return console.log('connect');
        });
        socket.on('reconnect', function () {
            return console.log('reconnect');
        });
        socket.on('connecting', function () {
            return console.log('connecting');
        });
        socket.on('reconnecting', function () {
            return console.log('reconnecting');
        });
        socket.on('connect_failed', function () {
            return console.log('connect failed');
        });
        socket.on('reconnect_failed', function () {
            return console.log('reconnect failed');
        });
        socket.on('close', function () {
            return console.log('close');
        });
        socket.on('disconnect', function () {
            return console.log('disconnect');
        });

        socket.on('login_success', function () {
            return console.log('login_success');
        });

        socket.on('synchronize', function () {
            return console.log('synchronize');
        });

        socket.on('user_join', (user) => {
            console.log('user_join ' + user.name);
        });

        socket.on('user_leave', (user) => {
            console.log('user_leave ' + user.name);
        });

        socket.on('message', (m) => {
            console.log('message ' + m.room);
        });

        socket.on('error', (code) => {
            console.log('error ' + code);
        });
    }
}