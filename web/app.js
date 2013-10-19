var $__getDescriptors = function(object) {
  var descriptors = {}, name, names = Object.getOwnPropertyNames(object);
  for (var i = 0; i < names.length; i++) {
    var name = names[i];
    descriptors[name] = Object.getOwnPropertyDescriptor(object, name);
  }
  return descriptors;
}, $__createClassNoExtends = function(object, staticObject) {
  var ctor = object.constructor;
  Object.defineProperty(object, 'constructor', {enumerable: false});
  ctor.prototype = object;
  Object.defineProperties(ctor, $__getDescriptors(staticObject));
  return ctor;
}, $__getProtoParent = function(superClass) {
  if (typeof superClass === 'function') {
    var prototype = superClass.prototype;
    if (Object(prototype) === prototype || prototype === null) return superClass.prototype;
  }
  if (superClass === null) return null;
  throw new TypeError();
}, $__createClass = function(object, staticObject, protoParent, superClass, hasConstructor) {
  var ctor = object.constructor;
  if (typeof superClass === 'function') ctor.__proto__ = superClass;
  if (!hasConstructor && protoParent === null) ctor = object.constructor = function() {};
  var descriptors = $__getDescriptors(object);
  descriptors.constructor.enumerable = false;
  ctor.prototype = Object.create(protoParent, descriptors);
  Object.defineProperties(ctor, $__getDescriptors(staticObject));
  return ctor;
}, $__superDescriptor = function(proto, name) {
  if (!proto) throw new TypeError('super is null');
  return Object.getPropertyDescriptor(proto, name);
}, $__superCall = function(self, proto, name, args) {
  var descriptor = $__superDescriptor(proto, name);
  if (descriptor) {
    if ('value'in descriptor) return descriptor.value.apply(self, args);
    if (descriptor.get) return descriptor.get.call(self).apply(self, args);
  }
  throw new TypeError("Object has no method '" + name + "'.");
};
Mustache.tags = ['[', ']'];
$.fn.exist = function() {
  return $(this).length > 0;
};
function tr(message) {
  return message;
}
function format(message) {
  var params = arguments[1] !== (void 0) ? arguments[1]: {};
  var key, value;
  for (key in params) if (params.hasOwnProperty(key)) {
    value = params[key];
    message = message.split('%' + key + '%').join(value);
  }
  return message;
}
;
var Sound = function() {
  'use strict';
  var $Sound = ($__createClassNoExtends)({
    constructor: function() {
      this.message = this.create(window.config.sound.message);
      this.join = this.create(window.config.sound.join);
    },
    create: function(file) {
      return new Howl({urls: [file]});
    }
  }, {});
  return $Sound;
}();
var Scroll = function() {
  'use strict';
  var $Scroll = ($__createClassNoExtends)({
    constructor: function(div) {
      var _this = this;
      this.div = div;
      this.able = true;
      this.scrolling = 0;
      this.div.on('scroll', function(e) {
        if (_this.scrolling === 0) {
          return _this.able = _this.div.scrollTop() + _this.div.outerHeight() + 10 > _this.div[0].scrollHeight;
        }
      });
    },
    down: function() {
      var _this = this;
      if (this.able) {
        this.scrolling += 1;
        return this.div.scrollTo('100%', 300, {onAfter: function() {
            return _this.scrolling -= 1;
          }});
      }
    },
    instantlyDown: function() {
      return this.div.scrollTo('100%', 0);
    }
  }, {});
  return $Scroll;
}();
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
var View = function() {
  'use strict';
  var $View = ($__createClassNoExtends)({
    constructor: function() {},
    render: function() {
      throw new TypeError('View class must implement render() method.');
    },
    toString: function() {
      return this.render();
    }
  }, {});
  return $View;
}();
var TabView = function($__super) {
  'use strict';
  var $__proto = $__getProtoParent($__super);
  var $TabView = ($__createClass)({
    constructor: function(id) {
      var title = arguments[1] !== (void 0) ? arguments[1]: '';
      var active = arguments[2] !== (void 0) ? arguments[2]: false;
      var count = arguments[3] !== (void 0) ? arguments[3]: 0;
      this.id = id;
      this.title = title;
      this.active = active;
      this.count = count;
    },
    render: function() {
      return template('chat/tab/room')({tab: this});
    }
  }, {}, $__proto, $__super, true);
  return $TabView;
}(View);
var UserTabView = function($__super) {
  'use strict';
  var $__proto = $__getProtoParent($__super);
  var $UserTabView = ($__createClass)({
    constructor: function(user) {
      $__superCall(this, $__proto, "constructor", ['user-' + user.id]);
      this.user = user;
    },
    render: function() {
      return template('chat/tab/user')({
        tab: this,
        user: this.user
      });
    }
  }, {}, $__proto, $__super, true);
  return $UserTabView;
}(TabView);
var MessageView = function($__super) {
  'use strict';
  var $__proto = $__getProtoParent($__super);
  var $MessageView = ($__createClass)({
    constructor: function(message) {
      var user = arguments[1] !== (void 0) ? arguments[1]: null;
      this.id = message.id;
      this.time = moment(message.datetime).format('hh:mm:ss');
      this.text = this.escape(message.text);
      this.room = message.room;
      if (user === null) {
        this.user = message.user;
      } else {
        this.user = user;
      }
    },
    render: function() {
      return template('chat/board/message')(this);
    },
    escape: function(html) {
      return html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&apos;');
    }
  }, {}, $__proto, $__super, true);
  return $MessageView;
}(View);
var LogView = function($__super) {
  'use strict';
  var $__proto = $__getProtoParent($__super);
  var $LogView = ($__createClass)({
    constructor: function(text) {
      $__superCall(this, $__proto, "constructor", [{
        id: 0,
        time: new Date(),
        user: null,
        text: text,
        room: 'main'
      }]);
    },
    render: function() {
      return template('chat/board/log')(this);
    }
  }, {}, $__proto, $__super, true);
  return $LogView;
}(MessageView);
var ChatBoardView = function($__super) {
  'use strict';
  var $__proto = $__getProtoParent($__super);
  var $ChatBoardView = ($__createClass)({
    constructor: function(room) {
      this.room = room;
    },
    render: function() {
      return template('chat/board/chat')(this);
    }
  }, {}, $__proto, $__super, true);
  return $ChatBoardView;
}(View);
var Server = function() {
  'use strict';
  var $Server = ($__createClassNoExtends)({
    constructor: function(server, namespace) {
      this.server = server;
      this.namespace = namespace;
    },
    send: function(text) {
      var room = arguments[1] !== (void 0) ? arguments[1]: 'main';
      if (text === '') {
        return;
      }
      $.post(window.config.api.send, {
        text: text,
        room: room
      }, null, 'json').done((function(res) {
        if (res.error !== false) {
          $(window).trigger('error', res.error);
        }
      }));
    },
    connect: function() {
      this.socket = io.connect(this.server + '/' + this.namespace, {query: 'namespace=' + this.namespace});
      this.bindSocket();
      this.socketLog();
    },
    login: function(auth) {
      this.socket.emit('login', auth);
    },
    join: function(room) {
      this.socket.emit('join', room);
    },
    bindSocket: function() {
      this.socket.on('connect', (function() {
        $(window).trigger('connect');
      }));
      this.socket.on('reconnect', (function() {
        $(window).trigger('reconnect');
      }));
      this.socket.on('synchronize', (function(users) {
        $(window).trigger('synchronize', users);
      }));
      this.socket.on('login_success', (function() {
        $(window).trigger('login_success');
      }));
      this.socket.on('user_join', (function(user) {
        $(window).trigger('user_join', user);
      }));
      this.socket.on('user_leave', (function(user) {
        $(window).trigger('user_leave', user);
      }));
      this.socket.on('message', (function(message) {
        $(window).trigger('message', message);
      }));
      this.socket.on('error', (function(error) {
        $(window).trigger('error', error);
      }));
    },
    socketLog: function() {
      var socket = this.socket;
      socket.on('connect', function() {
        return console.log('connect');
      });
      socket.on('reconnect', function() {
        return console.log('reconnect');
      });
      socket.on('connecting', function() {
        return console.log('connecting');
      });
      socket.on('reconnecting', function() {
        return console.log('reconnecting');
      });
      socket.on('connect_failed', function() {
        return console.log('connect failed');
      });
      socket.on('reconnect_failed', function() {
        return console.log('reconnect failed');
      });
      socket.on('close', function() {
        return console.log('close');
      });
      socket.on('disconnect', function() {
        return console.log('disconnect');
      });
      socket.on('login_success', function() {
        return console.log('login_success');
      });
      socket.on('synchronize', function() {
        return console.log('synchronize');
      });
      socket.on('user_join', (function(user) {
        console.log('user_join ' + user.name);
      }));
      socket.on('user_leave', (function(user) {
        console.log('user_leave ' + user.name);
      }));
      socket.on('message', (function(m) {
        console.log('message');
      }));
      socket.on('error', (function(code) {
        console.log('error ' + code);
      }));
    }
  }, {});
  return $Server;
}();
var Tabs = function() {
  'use strict';
  var $Tabs = ($__createClassNoExtends)({
    constructor: function(tabs) {
      var tabs = $('#tabs');
      this.dom = {
        rooms: tabs.find('#rooms'),
        users: tabs.find('#users'),
        getUserTab: function(user) {
          return $('#tab-user-' + user.id);
        }
      };
      this.select = {tabs: '#tabs .tab'};
      this.bind();
      this.addMainRoom();
    },
    bind: function() {
      $(window).on('synchronize', $.proxy(this.onSynchronize, this)).on('message', $.proxy(this.onMessage, this)).on('user_join', $.proxy(this.onUserJoin, this)).on('user_leave', $.proxy(this.onUserLeave, this)).on('user_update', $.proxy(this.onUserUpdate, this));
      $(document).on('click', this.select.tabs, $.proxy(this.onTabClick, this));
    },
    addMainRoom: function() {
      this.dom.rooms.append(new TabView('main', tr('Main'), true, 0).render());
    },
    onSynchronize: function(event) {
      for (var users = [], $__3 = 1; $__3 < arguments.length; $__3++) users[$__3 - 1] = arguments[$__3];
      this.dom.users.html('');
      for (var $__1 = $traceurRuntime.getIterator(users), $__2; !($__2 = $__1.next()).done;) {
        var user = $__2.value;
        {
          this.dom.users.append(new UserTabView(user).render());
        }
      }
    },
    onUserJoin: function(event, user) {
      if (!this.isUserTab(user)) {
        this.dom.users.append(new UserTabView(user).render());
      }
    },
    onUserLeave: function(event, user) {
      if (this.isUserTab(user)) {
        $('#tab-user-' + user.id).remove();
      }
    },
    onUserUpdate: function(event, user) {
      var tab = new UserTab(user);
      if (this.isUserTab(user)) {
        $('#tab-user-' + user.id).replaceWith(tab.render());
      } else {
        this.dom.users.append(tab.render());
      }
    },
    isUserTab: function(user) {
      return $('#tab-user-' + user.id).exist();
    },
    onTabClick: function(event) {
      console.log(event.target);
    }
  }, {});
  return $Tabs;
}();
var Application = function() {
  'use strict';
  var $Application = ($__createClassNoExtends)({
    constructor: function() {
      this.server = new Server(window.config.server, window.config.namespace);
      this.dom = {
        board: $('#board'),
        chat: {main: $('#chat-main')},
        textarea: $('#message')
      };
      this.bind();
      this.scroll = new Scroll(this.dom.board);
      this.users = {};
      this.sound = new Sound();
    },
    run: function() {
      this.server.connect();
    },
    bind: function() {
      $(window).on('connect', $.proxy(this.onConnect, this)).on('login_success', $.proxy(this.onLoginSuccess, this)).on('synchronize', $.proxy(this.onSynchronize, this)).on('message', $.proxy(this.onMessage, this)).on('user_join', $.proxy(this.onUserJoin, this)).on('user_leave', $.proxy(this.onUserLeave, this)).on('user_update', $.proxy(this.onUserUpdate, this));
      $(document).on('click.popover', '[data-popover]', $.proxy(this.onPopoverClick));
      $(this.dom.textarea).bind('keydown', 'return', $.proxy(this.onSend, this));
    },
    onSend: function(event) {
      this.server.send(this.dom.textarea.val(), 'main');
      this.dom.textarea.val('');
      event.stopPropagation();
      return false;
    },
    onConnect: function(event) {
      this.server.login(window.config.auth);
    },
    onLoginSuccess: function(event) {
      this.addRecentMessages();
      this.server.join(window.room);
    },
    onSynchronize: function(event) {
      for (var users = [], $__3 = 1; $__3 < arguments.length; $__3++) users[$__3 - 1] = arguments[$__3];
      for (var $__2 = $traceurRuntime.getIterator(users), $__1; !($__1 = $__2.next()).done;) {
        var user = $__1.value;
        {
          this.addUser(user);
        }
      }
    },
    onMessage: function(event, message) {
      var user;
      if (!this.isUserExist(message.user.id)) {
        return;
      }
      this.addMessage(new MessageView(message, this.getUser(message.user.id)));
      window.sound.message.play();
    },
    onMessageRemove: function(event, message) {},
    addRecentMessages: function() {
      for (var $__1 = $traceurRuntime.getIterator(window.recent), $__2; !($__2 = $__1.next()).done;) {
        var message = $__2.value;
        {
          this.addMessage(new MessageView(message));
        }
      }
      this.scroll.down();
    },
    onUserJoin: function(event, user) {
      this.addUser(user);
      this.addMessage(new LogView(format(tr('%name% joins the chat.'), {'name': user.name})));
      window.sound.join.play();
    },
    onUserLeave: function(event, user) {
      this.addMessage(new LogView(format(tr('%name% leaves the chat.'), {'name': user.name})));
    },
    onUserUpdate: function(event, user) {
      this.addUser(user);
    },
    onPopoverClick: function(event) {
      event.stopPropagation();
      var button = $(event.target);
      var id = button.attr('data-popover');
      var popover = this.popovers.get(id);
      if (null === popover) {
        var userId = button.attr('data-user-id');
        if (null === userId) {
          popover = new Popover.Popover(id);
        } else {
          user = this.users.get(userId);
          popover = new Popover.User(user);
        }
        this.popovers.add(popover);
      }
      popover.setButton(button);
      popover.toggle();
    },
    addMessage: function(messageView) {
      var room = arguments[1] !== (void 0) ? arguments[1]: 'main';
      var chat = this.getChat(room);
      chat.append(messageView.render());
      this.scroll.down();
    },
    getUser: function(id) {
      return this.users[id];
    },
    isUserExist: function(id) {
      return this.users[id] === void 0 ? false: true;
    },
    addUser: function(user) {
      this.users[user.id] = user;
    },
    getChat: function(room) {
      if (!this.dom.chat[room]) {
        this.dom.board.append(new ChatBoardView(room).render());
        this.dom.chat[room] = $('#chat-' + room);
      }
      return this.dom.chat[room];
    }
  }, {});
  return $Application;
}();

//@ sourceMappingURL=app.map