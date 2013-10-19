/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Application {
    constructor() {
        this.server = new Server(window.config.server, window.config.namespace);
        this.dom = {
            board: $('#board'),
            chat: {
                main: $('#chat-main')
            },
            textarea: $('#message'),
            body: $('body')
        };
        this.bind();
        this.scroll = new Scroll(this.dom.board);
        this.users = {};
        this.sound = new Sound();
    }

    run() {
        this.server.connect();
    }

    bind() {
        $(window)
            .on('connect', $.proxy(this.onConnect, this))
            .on('login_success', $.proxy(this.onLoginSuccess, this))
            .on('synchronize', $.proxy(this.onSynchronize, this))
            .on('message', $.proxy(this.onMessage, this))
            .on('user_join', $.proxy(this.onUserJoin, this))
            .on('user_leave', $.proxy(this.onUserLeave, this))
            .on('user_update', $.proxy(this.onUserUpdate, this));

        $(document)
            .on('click.popover', '[data-popover]', $.proxy(this.onPopoverClick, this))
            .on('click.profile', '[data-user-id]', $.proxy(this.onProfileClick, this))
            .on('click.username', '[data-user-name]', $.proxy(this.onUsernameClick, this));

        $(this.dom.textarea)
            .bind('keydown', 'return', $.proxy(this.onSend, this));
    }

    onSend(event) {
        this.server.send(this.dom.textarea.val(), 'main');
        this.dom.textarea.val('');

        event.stopPropagation();
        return false;
    }

    onConnect(event) {
        this.server.login(window.config.auth);
    }

    onLoginSuccess(event) {
        this.addRecentMessages();
        this.server.join(window.room);
    }

    onSynchronize(event, ...users) {
        for (var user of users) {
            this.addUser(user);
        }
    }

    onMessage(event, message) {
        var user;

        if (!this.isUserExist(message.user.id)) {
            return;
        }

        // Add message
        this.addMessage(new MessageView(message, this.getUser(message.user.id)));

        // Play sound
        window.sound.message.play();
    }

    onMessageRemove(event, message) {
        // Remove message from board
    }

    addRecentMessages() {
        for (var message of window.recent) {
            this.addMessage(new MessageView(message));
        }
        this.scroll.down();
    }

    onUserJoin(event, user) {
        // Add user
        this.addUser(user);

        // Add user login message
        this.addMessage(new LogView(format(tr('%name% joins the chat.'), {'name': user.name})));

        // Play sound
        window.sound.join.play();
    }

    onUserLeave(event, user) {
        // Add user logout message
        this.addMessage(new LogView(format(tr('%name% leaves the chat.'), {'name': user.name})));
    }

    onUserUpdate(event, user) {
        this.addUser(user);
        new UserProfileView(user).remove();
    }

    onPopoverClick(event) {
        event.stopPropagation();
        var button = $(event.target);
        var id = button.attr('data-popover');
        var popover = Popover.create(id, button);
        popover.toggle();
    }

    onProfileClick(event) {
        event.stopPropagation();
        var button = $(event.target);
        var user = this.getUser(button.attr('data-user-id'));
        if (user) {
            var view = new UserProfileView(user);

            if (!view.exist()) {
                this.dom.body.append(view.render());
            }

            var popover = Popover.create(view.id, button);
            popover.toggle();
        }
    }

    addMessage(messageView, room = 'main') {
        var chat = this.getChat(room);
        chat.append(messageView.render());
        this.scroll.down();
    }

    getUser(id) {
        return this.users[id];
    }

    isUserExist(id) {
        return this.users[id] === void 0 ? false : true;
    }

    addUser(user) {
        this.users[user.id] = user;
    }

    getChat(room) {
        if (!this.dom.chat[room]) {
            this.dom.board.append(new ChatBoardView(room).render());
            this.dom.chat[room] = $('#chat-' + room);
        }

        return this.dom.chat[room];
    }

    onUsernameClick(event) {
        var name = $(event.target).attr('data-user-name');
        this.dom.textarea.insertAtCaret(' ' + name + ' ');
    }
}