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


class UserView extends View {
    constructor(user) {
        this.user = user;
    }

    render() {
        return template('chat/tab/user')({user: this.user});
    }
}

class MessageView extends View {
    constructor(message) {
        console.log(message);
        this.data = message.data;
        this.id = message.id;
        this.time = moment(message.datetime).format('HH:mm:ss');
        this.text = this.filter(this.escape(message.data.text));
        this.user = message.user;
        this.for = message.for;

        this.spirit = false;
        if (this.text.match(/^∞/)) {
            this.text = this.text.substring(1);
            this.spirit = true;
            this.user.name = 'Chat Spirit';
            this.user.avatar = 'data:image/png;base64,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';
        }
    }

    render() {
        return template('chat/board/message')(this);
    }

    escape(html) {
        return html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&apos;');
    }

    filter(text) {
        for (var filter of window.chat.filters) {
            text = filter.filter(text);
        }
        return text;
    }
}

class LogView extends MessageView {
    constructor(text, level = 'default') {
        super({
            id: 0,
            time: new Date(),
            user: null,
            data: {text: text}
        });
        this.level = level;
    }

    render() {
        return template('chat/board/log')(this);
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

    update() {
        if (this.exist()) {
            this.element().replaceWith(this.render());
        }
    }

    element() {
        return $('#' + this.id);
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
