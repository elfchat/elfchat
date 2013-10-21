/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var templates = {};

function template(name) {
    if (!templates[name]) {
        var t = $('#view_' + name.split('/').join('_'));

        if (t.length === 0) {
            throw new Error('View "' + name + '" does not exist.');
        }

        templates[name] = Mustache.compile(t.html());
    }

    return templates[name];
}

class View {
    render() {
        throw new TypeError('View class must implement render() method.');
    }

    toString() {
        return this.render();
    }
}

class TabView extends View {
    constructor(id, title = '', active = false, count = 0) {
        this.id = id;
        this.title = title;
        this.active = active;
        this.count = count;
    }

    render() {
        return template('chat/tab/room')({
            tab: this
        });
    }
}

class UserTabView extends TabView {
    constructor(user) {
        super('user-' + user.id);
        this.user = user;
    }

    render() {
        return template('chat/tab/user')({tab: this, user: this.user});
    }
}

class MessageView extends View {
    constructor(message, user = null) {
        this.id = message.id;
        this.time = moment(message.datetime).format('hh:mm:ss');
        this.text = this.filter(this.escape(message.text));
        this.room = message.room;

        // Add user to message.
        if (user === null) {
            this.user = message.user;
        } else {
            this.user = user;
        }
    }

    render() {
        return template('chat/board/message')(this);
    }

    escape(html) {
        return html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&apos;');
    }

    filter(text) {
        for(var filter of window.chat.filters) {
            text = filter.filter(text);
        }
        return text;
    }
}

class LogView extends MessageView {
    constructor(text) {
        super({id: 0, time: new Date(), user: null, text, room: 'main'});
    }

    render() {
        return template('chat/board/log')(this);
    }
}

class ChatBoardView extends View {
    constructor(room) {
        this.room = room;
    }

    render() {
        return template('chat/board/chat')(this);
    }
}

class UserProfileView extends View {
    constructor(user) {
        this.id = 'profile-' + user.id;
        this.user = user;
    }

    render() {
        return template('chat/popover/profile')(this);
    }

    remove() {
        $('#' + this.id).remove();
    }

    exist() {
        return $('#' + this.id).exist();
    }
}

class EmotionTabView extends View {
    constructor(title, emotions) {
        this.title = title;
        this.emotions = emotions;
    }

    render() {
        return template('chat/emotion/tab')(this);
    }
}

class EmotionImageView extends View {
    constructor(src) {
        this.src = src;
    }

    render() {
        return template('chat/emotion/image')(this);
    }
}
