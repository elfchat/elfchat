/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Application {
    constructor() {
        this.server = new Server(window.config.server, window.config.namespace);
        this.users = {};
        this.filters = [];
        this.dom = {
            chat: $('#chat'),
            textarea: $('#message'),
            body: $('body')
        };
        this.scroll = new Scroll(this.dom.chat);
        this.sound = new Sound();
        this.bind();
        this.addFilters();
    }

    run() {
        notify.connecting.start();
        this.server.connect();
        this.addRecentMessages();
    }

    bind() {
        $(window)
            .on('connect', $.proxy(this.onConnect, this))
            .on('disconnect', $.proxy(this.onDisconnect, this))
            .on('login_success', $.proxy(this.onLoginSuccess, this))
            .on('synchronize', $.proxy(this.onSynchronize, this))
            .on('message', $.proxy(this.onMessage, this))
            .on('user_join', $.proxy(this.onUserJoin, this))
            .on('user_leave', $.proxy(this.onUserLeave, this))
            .on('user_update', $.proxy(this.onUserUpdate, this))
            .on('error', $.proxy(this.onError, this));

        $(document)
            .on('click.popover', '[data-popover]', $.proxy(this.onPopoverClick, this))
            .on('click.profile', '[data-user-id]', $.proxy(this.onProfileClick, this))
            .on('click.username', '[data-user-name]', $.proxy(this.onUsernameClick, this));

        $(this.dom.textarea)
            .bind('keydown', 'return', $.proxy(this.onSend, this));
    }

    addFilters() {
        this.filters = [
            new BBCodeFilter(),
            new UriFilter({chat: this.dom.board}),
            new EmotionFilter(EmotionList),
            new RestrictionFilter()
        ];
    }

    onSend(event) {
        this.server.send(this.dom.textarea.val(), window.room);
        this.dom.textarea.val('');

        event.stopPropagation();
        return false;
    }

    onConnect(event) {
        notify.connecting.stop();
        this.server.login(window.config.auth);
    }

    onDisconnect(event) {
        notify.connecting.start();
    }

    onLoginSuccess(event) {
        this.server.join(window.room);
    }

    onSynchronize(event, ...users) {
        for (var user of users) {
            this.addUser(user);
        }
    }

    onMessage(event, message) {
        // TODO: remove this in future:
        console.log(message);
        if (!this.isUserExist(message.user.id)) {
            return;
        }

        var user = this.getUser(message.user.id);
        var messageView = new MessageView(message, user);
        var isPrivate = message.room.match(/^private-(.*)$/);

        if (isPrivate === null) {
            this.addMessage(messageView, message.room);
        } else {
            if (user.id === window.user.id) {
                this.addMessage(messageView, message.room);
            } else {
                this.addMessage(messageView, 'private-' + user.id);
            }
        }

        window.sound.message.play();
    }

    onMessageRemove(event, message) {
        // Remove message from board
    }

    addRecentMessages() {
        for (var message of window.recent) {
            if(this.getUser(message.user.id) === void 0) {
                this.addUser(message.user);
            }
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

    addMessage(messageView) {
        this.dom.chat.append(messageView.render());
        this.scroll.down();
    }

    getUser(id) {
        return this.users[id];
    }

    isUserExist(id) {
        return this.users[id] !== void 0;
    }

    addUser(user) {
        this.users[user.id] = user;
    }

    onUsernameClick(event) {
        var name = $(event.target).attr('data-user-name');
        this.dom.textarea.insertAtCaret(' ' + name + ' ');
    }

    onError(event, error) {
        notify.error(tr(error));
    }
}