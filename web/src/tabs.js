/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Tabs {
    constructor() {
        this.dom = {
            users: $('#users'),
            getUser(user) {
                return $('#user-' + user.id);
            }
        };

        this.bind();
    }

    bind() {
        $(window)
            .on('synchronize', $.proxy(this.onSynchronize, this))
            .on('user_join', $.proxy(this.onUserJoin, this))
            .on('user_leave', $.proxy(this.onUserLeave, this))
            .on('user_update', $.proxy(this.onUserUpdate, this));
    }

    onSynchronize(event, ...users) {
        // Clear all users tabs
        this.dom.users.html('');

        for (var user of users) {
            // Add user tab
            this.dom.users.append(new UserView(user).render());
        }
    }

    onUserJoin(event, user) {
        var tab = this.dom.getUser(user);
        var view = new UserView(user);
        if (tab.exist()) {
            tab.remove();
        }
        this.dom.users.append(view.render());
    }

    onUserLeave(event, user) {
        var tab = this.dom.getUser(user);
        if (tad.exist()) {
            tab.remove();
        }
    }

    onUserUpdate(event, user) {
        var tab = this.dom.getUser(user);
        var view = new UserView(user);
        if (tab.exist()) {
            tab.replaceWith(view.render());
        } else {
            this.dom.users.append(view.render());
        }
    }
}