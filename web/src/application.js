/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Application {
    constructor(server) {
        this.server = server;
        this.filters = [];

        this.dom = {
            chat: $('#chat'),
            textarea: $('#message'),
            body: $('body')
        };

        $(window)
            .on('connect', $.proxy(this.onConnect, this))
            .on('disconnect', $.proxy(this.onDisconnect, this))
            .on('message', $.proxy(this.onMessage, this))
            .on('user_join', $.proxy(this.onUserJoin, this))
            .on('user_leave', $.proxy(this.onUserLeave, this))
            .on('error', $.proxy(this.onError, this));

        $(document)
            .on('click.popover', '[data-popover]', $.proxy(this.onPopoverClick, this))
            .on('click.profile', '[data-user-id]', $.proxy(this.onProfileClick, this))
            .on('click.username', '[data-user-name]', $.proxy(this.onUsernameClick, this));

        $(this.dom.textarea)
            .bind('keydown', 'return', $.proxy(this.onSend, this));

        $('[data-action="send"]')
            .on('click', $.proxy(this.onSend, this));

        this.filters = [
            new BBCodeFilter(),
            new UriFilter(),
            new ImageFilter(),
            new EmotionFilter(EmotionList),
            new RestrictionFilter()
        ];
    }

    run() {
        notify.connecting.start();
        this.server.connect();
        this.addRecentMessages();
    }

    onSend(event) {
        event.stopPropagation();
        var message,
            userId,
            button = $(event.target);

        if('' === (message = this.dom.textarea.val())) {
            return false;
        }

        if (undefined === (userId = button.attr('data-private'))) {
            this.server.send(message);
        } else {
            this.server.sendPrivate(userId, message);
        }

        this.dom.textarea.val('').focus();
        return false;
    }

    onConnect(event) {
        notify.connecting.stop();
    }

    onDisconnect(event) {
        notify.connecting.start();
    }

    onMessage(event, message) {
        this.addMessage(message);
        window.sound.message.play();
    }

    onMessageRemove(event, message) {
        // Remove message from chat
    }

    addRecentMessages() {
        for (var message of window.recent) {
            this.addMessage(message);
        }
        window.scroll.instantlyDown();
    }

    onUserJoin(event, user) {
        // Add user login message
        this.addLog(format(tr('%name% joins the chat.'), {'name': user.name}));

        // Play sound
        window.sound.join.play();
    }

    onUserLeave(event, user) {
        // Add user logout message
        this.addLog(format(tr('%name% leaves the chat.'), {'name': user.name}));
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
        var user = window.users.getUser(button.attr('data-user-id'));

        if (user) {
            var view = new UserProfileView(user);

            if (!view.exist()) {
                this.dom.body.append(view.render());
            }

            var popover = Popover.create(view.id, button);
            popover.toggle();
        }
    }

    addMessage(message) {
        if (message !== undefined) {
            this.dom.chat.append(new MessageView(message).render());
            window.scroll.down();
        }
    }

    addLog(log) {
        if (log !== undefined) {
            this.dom.chat.append(new LogView(log).render());
            window.scroll.down();
        }
    }

    onUsernameClick(event) {
        var name = $(event.target).attr('data-user-name');
        this.dom.textarea.insertAtCaret(' ' + name + ' ');
    }

    onError(event, error) {
        notify.error(tr(error));
    }

    updateUser() {
        // TODO: Create this method.
    }
}