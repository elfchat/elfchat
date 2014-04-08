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
        this.id = message.id;
        this.time = moment(message.datetime).format('HH:mm:ss');
        this.text = this.filter(this.escape(message.text));
        this.user = message.user;

        this.spirit = false;
        if (this.text.match(/^∞/)) {
            this.text = this.text.substring(1);
            this.spirit = true;
        }
    }

    render() {
        return template(this.spirit ? 'chat/board/spirit' : 'chat/board/message')(this);
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
    constructor(text) {
        super({id: 0, time: new Date(), user: null, text});
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
