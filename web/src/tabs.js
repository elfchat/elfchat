/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Tabs {
    constructor() {
        var tabs = $('#tabs');
        this.dom = {
            rooms: tabs.find('#rooms'),
            users: tabs.find('#users'),
            getUserTab(user) {
                return $('#tab-user-' + user.id);
            }
        };
        this.select = {
            tabs: '#tabs .tab'
        };
        this.current = 'tab-main';

        this.bind();
        this.addMainRoom();
    }

    bind() {
        $(window)
            .on('synchronize', $.proxy(this.onSynchronize, this))
            .on('user_join', $.proxy(this.onUserJoin, this))
            .on('user_leave', $.proxy(this.onUserLeave, this))
            .on('user_update', $.proxy(this.onUserUpdate, this));

        $(document)
            .on('click', this.select.tabs, $.proxy(this.onTabClick, this));
    }

    addMainRoom() {
        this.dom.rooms.append(new TabView('main', tr('Main'), true, 0).render());
    }

    onSynchronize(event, ...users) {
        // Clear all users tabs
        this.dom.users.html('');

        for (var user of users) {
            // Add user tab
            this.dom.users.append(new UserTabView(user).render());
        }
    }

    onUserJoin(event, user) {
        // Add user tab
        if (!this.isUserTab(user)) {
            this.dom.users.append(new UserTabView(user).render());
        }
    }

    onUserLeave(event, user) {
        if (this.isUserTab(user)) {
            $('#tab-user-' + user.id).remove();
        }
    }

    onUserUpdate(event, user) {
        var tab = new UserTab(user);
        if (this.isUserTab(user)) {
            $('#tab-user-' + user.id).replaceWith(tab.render());
        } else {
            this.dom.users.append(tab.render());
        }
    }

    increaseCounter(room) {
        var tab = $('[data-room="' + room + '"]');
        if (tab.exist() && !tab.hasClass('active')) {
            var count = tab.find('.count');
            var val = parseInt(count.text()) || 0;
            count.text(val + 1).show();
        }
    }

    isUserTab(user) {
        return $('#tab-user-' + user.id).exist();
    }

    onTabClick(event) {
        this.selectTab($(event.currentTarget));
    }

    selectTab(tab) {
        var old = $('#' + this.current);

        // Change active tab
        old.removeClass('active');
        this.current = tab.attr('id');
        tab.addClass('active');

        // Reset counter
        tab.find('.count').text('0').hide();

        // Switch chat rooms and save current room id
        window.chat.getChatRoom(window.room).hide();
        window.room = tab.attr('data-room');
        window.chat.getChatRoom(window.room).show();

        // Scroll and focus
        window.chat.scroll.instantlyDown();
        window.chat.dom.textarea.focus();
    }
}