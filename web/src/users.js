/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Users {
    constructor() {
        this.dom = {
            users: $('#users'),
            user(user) {
                return $('#user-' + user.id);
            }
        };

        // Users data
        this.users = {};

        // Bind to server commands
        $(window)
            .on('synchronize', $.proxy(this.onSynchronize, this))
            .on('user_join', $.proxy(this.onUserJoin, this))
            .on('user_leave', $.proxy(this.onUserLeave, this))
            .on('user_update', $.proxy(this.onUserUpdate, this));

        // Collect user data from recent messages
        for (var message of window.recent) {
            this.addUser(message.user);
        }
    }

    onSynchronize(event, users) {
        // Clear all users tabs
        this.dom.users.html('');

        for (var user of users) {
            // Save user data
            this.addUser(user);

            // Add user tab
            this.dom.users.append(new UserView(user).render());
        }
    }

    onUserJoin(event, user) {
        // Save user data
        this.addUser(user);

        // Add to DOM
        var tab = this.dom.user(user);
        var view = new UserView(user);
        if (tab.exist()) {
            tab.remove();
        }
        this.dom.users.append(view.render());
    }

    onUserLeave(event, user) {
        // Remove from DOM
        var tab = this.dom.user(user);
        if (tab.exist()) {
            tab.remove();
        }
    }

    onUserUpdate(event, user) {
        // Update user data
        this.addUser(user);

        // Update DOM
        var tab = this.dom.user(user);
        var view = new UserView(user);

        if (tab.exist()) {
            tab.replaceWith(view.render(user));
        } else {
            this.dom.users.append(view.render(user));
        }
    }

    getUser(id) {
        if (this.isUserExist(id)) {
            return this.users[id];
        } else {
            // TODO: Load from server data of user.
            throw new Error('Need to load data from server.');
        }
    }

    isUserExist(id) {
        return this.users[id] !== void 0;
    }

    addUser(user) {
        this.users[user.id] = user;
    }
}