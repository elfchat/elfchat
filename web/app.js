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
  return window.lang[message] ? window.lang[message]: message;
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
function Notify() {
  this.error = function(error) {
    return $.notification({
      title: tr('Error'),
      content: error,
      icon: 'fa fa-info',
      error: true
    });
  };
  this.alert = function(text) {
    return $.notification({
      title: tr('Info'),
      content: text,
      icon: 'fa fa-info'
    });
  };
  var connecting = null;
  this.connecting = {
    start: function() {
      if (connecting != null) {
        connecting.hide();
      }
      return connecting = $.notification({
        content: tr('Connecting'),
        icon: 'fa fa-spinner fa-spin'
      });
    },
    stop: function() {
      return connecting.remove();
    }
  };
}
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
      this.text = this.filter(this.escape(message.text));
      this.room = message.room;
      if (user === null) {
        this.user = message.user;
      } else {
        this.user = user;
      }
      this.spirit = false;
      if (this.text.match(/^∞/)) {
        this.text = this.text.substring(1);
        this.spirit = true;
      }
    },
    render: function() {
      return template(this.spirit ? 'chat/board/spirit': 'chat/board/message')(this);
    },
    escape: function(html) {
      return html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&apos;');
    },
    filter: function(text) {
      for (var $__1 = $traceurRuntime.getIterator(window.chat.filters), $__2; !($__2 = $__1.next()).done;) {
        var filter = $__2.value;
        {
          text = filter.filter(text);
        }
      }
      return text;
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
      var visible = arguments[1] !== (void 0) ? arguments[1]: true;
      this.room = room;
      this.visible = visible;
    },
    render: function() {
      return template('chat/board/chat')(this);
    }
  }, {}, $__proto, $__super, true);
  return $ChatBoardView;
}(View);
var UserProfileView = function($__super) {
  'use strict';
  var $__proto = $__getProtoParent($__super);
  var $UserProfileView = ($__createClass)({
    constructor: function(user) {
      this.id = 'profile-' + user.id;
      this.user = user;
    },
    render: function() {
      return template('chat/popover/profile')(this);
    },
    remove: function() {
      $('#' + this.id).remove();
    },
    exist: function() {
      return $('#' + this.id).exist();
    }
  }, {}, $__proto, $__super, true);
  return $UserProfileView;
}(View);
var EmotionTabView = function($__super) {
  'use strict';
  var $__proto = $__getProtoParent($__super);
  var $EmotionTabView = ($__createClass)({
    constructor: function(title, emotions) {
      this.title = title;
      this.emotions = emotions;
    },
    render: function() {
      return template('chat/emotion/tab')(this);
    }
  }, {}, $__proto, $__super, true);
  return $EmotionTabView;
}(View);
var EmotionImageView = function($__super) {
  'use strict';
  var $__proto = $__getProtoParent($__super);
  var $EmotionImageView = ($__createClass)({
    constructor: function(src) {
      this.src = src;
    },
    render: function() {
      return template('chat/emotion/image')(this);
    }
  }, {}, $__proto, $__super, true);
  return $EmotionImageView;
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
      this.socket.on('disconnect', (function() {
        $(window).trigger('disconnect');
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
        console.log('message ' + m.room);
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
    constructor: function() {
      var tabs = $('#tabs');
      this.dom = {
        rooms: tabs.find('#rooms'),
        users: tabs.find('#users'),
        getUserTab: function(user) {
          return $('#tab-user-' + user.id);
        }
      };
      this.select = {tabs: '#tabs .tab'};
      this.current = 'tab-main';
      this.bind();
      this.addMainRoom();
    },
    bind: function() {
      $(window).on('synchronize', $.proxy(this.onSynchronize, this)).on('user_join', $.proxy(this.onUserJoin, this)).on('user_leave', $.proxy(this.onUserLeave, this)).on('user_update', $.proxy(this.onUserUpdate, this));
      $(document).on('click', this.select.tabs, $.proxy(this.onTabClick, this));
    },
    addMainRoom: function() {
      this.dom.rooms.append(new TabView('main', tr('Main'), true, 0).render());
    },
    onSynchronize: function(event) {
      for (var users = [], $__3 = 1; $__3 < arguments.length; $__3++) users[$__3 - 1] = arguments[$__3];
      this.dom.users.html('');
      for (var $__2 = $traceurRuntime.getIterator(users), $__1; !($__1 = $__2.next()).done;) {
        var user = $__1.value;
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
    increaseCounter: function(room) {
      var tab = $('[data-room="' + room + '"]');
      if (tab.exist() && !tab.hasClass('active')) {
        var count = tab.find('.count');
        var val = parseInt(count.text()) || 0;
        count.text(val + 1).show();
      }
    },
    isUserTab: function(user) {
      return $('#tab-user-' + user.id).exist();
    },
    onTabClick: function(event) {
      this.selectTab($(event.currentTarget));
    },
    selectTab: function(tab) {
      var old = $('#' + this.current);
      old.removeClass('active');
      this.current = tab.attr('id');
      tab.addClass('active');
      tab.find('.count').text('0').hide();
      window.chat.getChatRoom(window.room).hide();
      window.room = tab.attr('data-room');
      window.chat.getChatRoom(window.room).show();
      window.chat.scroll.instantlyDown();
      window.chat.dom.textarea.focus();
    }
  }, {});
  return $Tabs;
}();
var Emotion = function() {
  'use strict';
  var $Emotion = ($__createClassNoExtends)({
    constructor: function() {
      this.dom = {
        popover: $('#emotions'),
        content: $('#emotions .content'),
        button: $('footer .emotions'),
        textarea: $('footer textarea')
      };
      this.currentTab = 0;
      if (EmotionCatalog) {
        this.tabs = new EmotionTabs(EmotionCatalog);
        this.bind();
      }
    },
    onButtonClick: function(event) {
      this.render();
    },
    render: function() {
      this.dom.content.html(this.tabs.render());
      var wait = [];
      this.dom.content.find('img').each(function(index, img) {
        var deferred = $.Deferred();
        img.onload = function() {
          return deferred.resolve();
        };
        wait.push(deferred);
      });
      $.when.apply($, wait).done(function() {
        Popover.get('emotions').reposition();
      });
    },
    bind: function() {
      var _this = this;
      this.dom.button.one('click', function() {
        _this.render();
      });
      _this.dom.button.click(function() {
        _this.dom.textarea.focus();
      });
      _this.dom.popover.on('click', function() {
        return _this.dom.textarea.focus();
      });
      _this.dom.popover.on('click', '.left', function() {
        _this.currentTab--;
        if (_this.currentTab < 0) {
          _this.currentTab = _this.tabs.max - 1;
        }
        return _this.dom.content.scrollTo(_this.getTab(_this.currentTab), 300);
      });
      _this.dom.popover.on('click', '.right', function() {
        _this.currentTab++;
        if (_this.currentTab >= _this.tabs.max) {
          _this.currentTab = 0;
        }
        return _this.dom.content.scrollTo(_this.getTab(_this.currentTab), 300);
      });
      _this.dom.popover.on('click', '[data-emotion]', function() {
        var emotion = $(this).attr('data-emotion');
        return _this.dom.textarea.insertAtCaret(" " + emotion + " ");
      });
    },
    getTab: function(i) {
      return this.dom.content.find(".tab:eq(" + i + ")");
    }
  }, {});
  return $Emotion;
}();
var EmotionTabs = function() {
  'use strict';
  var $EmotionTabs = ($__createClassNoExtends)({
    constructor: function(catalog) {
      this.pertab = 28;
      this.catalog = catalog;
      this.n = 0;
      this.emotions = [];
      this.max = 0;
    },
    render: function() {
      var html = '';
      for (var title in this.catalog) if (this.catalog.hasOwnProperty(title)) {
        var tab = this.catalog[title];
        for (var $__1 = $traceurRuntime.getIterator(tab), $__2; !($__2 = $__1.next()).done;) {
          var emotion = $__2.value;
          {
            this.emotions.push(emotion);
            if (this.n >= this.pertab - 1) {
              html += this.renderTab(title, this.emotions);
            } else {
              this.n++;
            }
          }
        }
        if (this.n !== 0) {
          html += this.renderTab(title, this.emotions);
        }
      }
      return html;
    },
    renderTab: function(title, emotions) {
      this.n = 0;
      this.emotions = [];
      this.max++;
      return new EmotionTabView(tr(title), emotions).render();
    }
  }, {});
  return $EmotionTabs;
}();
var Filter = function() {
  'use strict';
  var $Filter = ($__createClassNoExtends)({
    constructor: function() {},
    filter: function(html) {
      return this.explode(html, {
        tag: $.proxy(this.tag, this),
        text: $.proxy(this.text, this)
      });
    },
    explode: function(html, map) {
      var array, i, length, part, tag, text, _i, _ref, _ref1;
      if (map == null) {
        map = null;
      }
      array = [''];
      length = html.length;
      part = 0;
      for (i = _i = 0; 0 <= length ? _i <= length: _i >= length; i = 0 <= length ? ++_i: --_i) {
        if (html.charAt(i) === '<') {
          array.push('');
          part++;
        }
        array[part] += html.charAt(i);
        if (html.charAt(i) === '>') {
          array.push('');
          part++;
        }
      }
      tag = (_ref = map.tag) != null ? _ref: function(value) {
        return value;
      };
      text = (_ref1 = map.text) != null ? _ref1: function(value) {
        return value;
      };
      array = array.map(function(value, n) {
        if (value === '') {
          return value;
        }
        if (n % 2 === 1) {
          return tag(value);
        } else {
          return text(value);
        }
      });
      return array.join('');
    }
  }, {});
  return $Filter;
}();
var BBCodeFilter = function($__super) {
  'use strict';
  var $__proto = $__getProtoParent($__super);
  var $BBCodeFilter = ($__createClass)({
    constructor: function() {
      $__superCall(this, $__proto, "constructor", arguments);
    },
    text: function(text) {
      text = text.replace(/\[bg=([#0-9a-z]{1,20})\]((?:.(?!\[bg))*)\[\/bg\]/ig, '<span style="background-color:$1;">$2</span>');
      text = text.replace(/\[color=([#0-9a-z]{1,20})\]((?:.(?!\[color))*)\[\/color\]/ig, '<span style="color:$1;">$2</span>');
      text = text.replace(/\[b\]((?:.(?!\[b\]))*)\[\/b\]/ig, '<b>$1</b>');
      text = text.replace(/\[i\]((?:.(?!\[i\]))*)\[\/i\]/ig, '<i>$1</i>');
      text = text.replace(/\[s\]((?:.(?!\[s\]))*)\[\/s\]/ig, '<s>$1</s>');
      text = text.replace(/\[m\]((?:.(?!\[s\]))*)\[\/m\]/ig, '<marquee>$1</marquee>');
      text = text.replace(/\[quote([^\]]*)\]((?:.(?!\[quote))*)\[\/quote\]/ig, function(m, p1, p2) {
        var info, msg, name, quote, time;
        info = '';
        quote = '';
        msg = p1.match(/msg=&quot;([0-9]*)&quot;/);
        if (msg !== null && msg[1] !== '') {
          quote = ' ref="' + msg[1] + '"';
        }
        time = p1.match(/time=&quot;([\.:0-9]*)&quot;/);
        if (time !== null && time[1] !== '') {
          info += '<i>' + time[1] + '</i> ';
        }
        name = p1.match(/name=&quot;(.*)&quot;/);
        if (name !== null && name[1] !== '') {
          info += name[1];
        }
        if (info !== '') {
          info = '&copy; ' + info + ': ';
        }
        return '<blockquote' + quote + '>' + info + p2 + '</blockquote>';
      });
      return text;
    }
  }, {}, $__proto, $__super, false);
  return $BBCodeFilter;
}(Filter);
var RestrictionFilter = function($__super) {
  'use strict';
  var $__proto = $__getProtoParent($__super);
  var $RestrictionFilter = ($__createClass)({
    constructor: function() {
      this.maximumLengthOfWords = 100;
      this.maximumNumberOfLines = 20;
    },
    filter: function(html) {
      var linesCount, _this = this;
      html = this.explode(html, {text: function(text) {
          return text.replace(new RegExp('[^\\s]{' + _this.maximumLengthOfWords + ',}', 'g'), function(all) {
            return all.substr(0, 20) + '...' + all.substr(all.length - 20, all.length);
          });
        }});
      html = html.replace(/([\s]{100,})/g, function(all) {
        return all.substr(0, 100);
      });
      html = html.replace(/(\n){3,}/g, '\n\n');
      linesCount = 0;
      html = html.replace(/[\n\r\t]/g, function(all) {
        if (++linesCount < _this.maximumNumberOfLines) {
          return '\n';
        } else {
          return ' ';
        }
      });
      return html;
    }
  }, {}, $__proto, $__super, true);
  return $RestrictionFilter;
}(Filter);
var UriFilter = function($__super) {
  'use strict';
  var $__proto = $__getProtoParent($__super);
  var $UriFilter = ($__createClass)({
    constructor: function(init) {
      var _ref, _ref1, _ref2;
      this.chat = (_ref = init.chat) != null ? _ref: $(window);
      this.imageable = (_ref1 = init.imageable) != null ? _ref1: true;
      this.imageCount = 0;
      this.maxImages = (_ref2 = init.maxImages) != null ? _ref2: 3;
      this.regex = /(https?):\/\/((?:[a-z0-9.-]|%[0-9A-F]{2}){3,})(?::(\d+))?((?:\/(?:[a-z0-9-._~!$&'()*+,;=:@]|%[0-9A-F]{2})*)*)(?:\?((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9A-F]{2})*))?(?:#((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9A-F]{2})*))?/ig;
    },
    text: function(text) {
      this.images = 0;
      return text = text.replace(this.regex, $.proxy(this.callback, this));
    },
    callback: function(uri, p1, p2, p3, p4, p5, p6, p7, p8, p9) {
      var ext, id, img, text, _ref, _this = this;
      text = uri;
      ext = uri.match(/\.([a-z0-9]+)$/i);
      if (((_ref = ext != null ? ext[1]: void 0) === 'jpg' || _ref === 'jpeg' || _ref === 'png' || _ref === 'gif') && this.imageable && this.images++ < this.maxImages) {
        this.imageCount += 1;
        id = 'external-img-' + this.imageCount;
        text = "<img class=\"external\" id=\"" + id + "\" src=\"" + uri + "\">";
        img = new Image();
        img.src = uri;
        img.onload = function() {
          return (function(id, uri) {
            var height;
            height = $('#' + id).height();
            return _this.chat.scrollTo('+=' + height);
          })(id, uri);
        };
      }
      return "<a href=\"" + uri + "\" target=\"_blank\">" + text + "</a>";
    }
  }, {}, $__proto, $__super, true);
  return $UriFilter;
}(Filter);
var EmotionFilter = function($__super) {
  'use strict';
  var $__proto = $__getProtoParent($__super);
  var $EmotionFilter = ($__createClass)({
    constructor: function(list) {
      this.list = list;
      this.max = 20;
    },
    text: function(text) {
      var _this = this;
      var count = 0;
      text = text.replace(/&apos;/g, "'");
      for (var $__2 = $traceurRuntime.getIterator(this.list), $__1; !($__1 = $__2.next()).done;) {
        var row = $__1.value;
        {
          var regexp = row[0];
          var src = row[1];
          text = text.replace(regexp, (function(str) {
            count++;
            if (count > _this.max) {
              return str;
            } else {
              return new EmotionImageView(src).render();
            }
          }));
        }
      }
      text = text.replace(/'/g, '&apos;');
      return text;
    }
  }, {}, $__proto, $__super, true);
  return $EmotionFilter;
}(Filter);
var popovers = {};
var Popover = function() {
  'use strict';
  var $Popover = ($__createClassNoExtends)({
    constructor: function(id) {
      var button = arguments[1] !== (void 0) ? arguments[1]: null;
      var box = arguments[2] !== (void 0) ? arguments[2]: '.box';
      var _this = this;
      this.id = id;
      this.box = $(box);
      this.margin = 10;
      this.onTop = 'top';
      this.onBottom = 'bottom';
      this.onLeft = 'left';
      this.onRight = 'right';
      this.autohide = false;
      if (button !== null) {
        this.on(button);
      }
      $(window).resize(function() {
        return _this.reposition();
      });
      $('body').mouseup((function(event) {
        if (_this.autohide && _this.getPopover().has(event.target).length === 0) {
          _this.hide();
        }
      }));
    },
    on: function(button) {
      var _this = this;
      this.button = $(button);
      this.button.mousedown((function(event) {
        _this.autohide = false;
      }));
      return this;
    },
    reposition: function() {
      var arrow, arrowPosition, boxSize, buttonOffset, buttonSize, offset, over, popover, popoverPosition, popoverSize;
      arrow = this.getArrow();
      popover = this.getPopover();
      boxSize = {
        width: this.box.width(),
        height: this.box.height()
      };
      buttonOffset = this.button.offset();
      buttonSize = {
        width: this.button.outerWidth(),
        height: this.button.outerHeight()
      };
      popoverSize = {
        width: popover.outerWidth(),
        height: popover.outerHeight()
      };
      if (popover.hasClass(this.onLeft) || popover.hasClass(this.onRight)) {
        if (popover.hasClass(this.onLeft)) {
          popoverPosition = {
            top: buttonOffset.top - (popoverSize.height / 2) + (buttonSize.height / 2),
            left: buttonOffset.left - popoverSize.width
          };
        } else {
          popoverPosition = {
            top: buttonOffset.top - (popoverSize.height / 2) + (buttonSize.height / 2),
            left: buttonOffset.left + buttonSize.width
          };
        }
        arrowPosition = {top: popoverSize.height / 2};
        if ((over = popoverPosition.top + popoverSize.height) > boxSize.height) {
          offset = over - boxSize.height + this.margin;
          popoverPosition.top -= offset;
          arrowPosition.top += offset;
        }
        if ((over = popoverPosition.top) < 0) {
          offset = - over + this.margin;
          popoverPosition.top += offset;
          arrowPosition.top -= offset;
        }
      } else {
        popoverPosition = {
          top: buttonSize.height + buttonOffset.top,
          left: buttonOffset.left - (popoverSize.width / 2) + (buttonSize.width / 2)
        };
        arrowPosition = {left: popoverSize.width / 2};
        if (popoverPosition.top + popoverSize.height > boxSize.height || popover.hasClass(this.onTop)) {
          popoverPosition.top = buttonOffset.top - popoverSize.height;
        }
        if ((over = popoverPosition.left + popoverSize.width) > boxSize.width) {
          offset = over - boxSize.width + this.margin;
          popoverPosition.left -= offset;
          arrowPosition.left += offset;
        }
        if ((over = popoverPosition.left) < 0) {
          offset = - over + this.margin;
          popoverPosition.left += offset;
          arrowPosition.left -= offset;
        }
      }
      popover.css(popoverPosition);
      arrow.css(arrowPosition);
    },
    show: function() {
      this.reposition();
      this.getPopover().show();
      return this.autohide = true;
    },
    hide: function() {
      return this.getPopover().hide();
    },
    toggle: function() {
      this.reposition();
      this.getPopover().toggle();
      return this.autohide = true;
    },
    getPopover: function() {
      return $('#' + this.id);
    },
    getArrow: function() {
      return this.getPopover().find('.arrow');
    }
  }, {
    create: function(id, button) {
      if (popovers[id]) {
        return popovers[id].on(button);
      } else {
        return popovers[id] = new Popover(id, button);
      }
    },
    get: function(id) {
      return popovers[id];
    }
  });
  return $Popover;
}();
var Application = function() {
  'use strict';
  var $Application = ($__createClassNoExtends)({
    constructor: function() {
      this.server = new Server(window.config.server, window.config.namespace);
      this.users = {};
      this.filters = [];
      this.dom = {
        board: $('#board'),
        chat: {main: $('#chat-main')},
        textarea: $('#message'),
        body: $('body')
      };
      this.scroll = new Scroll(this.dom.board);
      this.sound = new Sound();
      this.bind();
      this.addFilters();
    },
    run: function() {
      notify.connecting.start();
      this.server.connect();
      this.addRecentMessages();
    },
    bind: function() {
      $(window).on('connect', $.proxy(this.onConnect, this)).on('disconnect', $.proxy(this.onDisconnect, this)).on('login_success', $.proxy(this.onLoginSuccess, this)).on('synchronize', $.proxy(this.onSynchronize, this)).on('message', $.proxy(this.onMessage, this)).on('user_join', $.proxy(this.onUserJoin, this)).on('user_leave', $.proxy(this.onUserLeave, this)).on('user_update', $.proxy(this.onUserUpdate, this)).on('error', $.proxy(this.onError, this));
      $(document).on('click.popover', '[data-popover]', $.proxy(this.onPopoverClick, this)).on('click.profile', '[data-user-id]', $.proxy(this.onProfileClick, this)).on('click.username', '[data-user-name]', $.proxy(this.onUsernameClick, this));
      $(this.dom.textarea).bind('keydown', 'return', $.proxy(this.onSend, this));
    },
    addFilters: function() {
      this.filters = [new BBCodeFilter(), new UriFilter({chat: this.dom.board}), new EmotionFilter(EmotionList), new RestrictionFilter()];
    },
    onSend: function(event) {
      this.server.send(this.dom.textarea.val(), window.room);
      this.dom.textarea.val('');
      event.stopPropagation();
      return false;
    },
    onConnect: function(event) {
      notify.connecting.stop();
      this.server.login(window.config.auth);
    },
    onDisconnect: function(event) {
      notify.connecting.start();
    },
    onLoginSuccess: function(event) {
      this.server.join(window.room);
    },
    onSynchronize: function(event) {
      for (var users = [], $__3 = 1; $__3 < arguments.length; $__3++) users[$__3 - 1] = arguments[$__3];
      for (var $__1 = $traceurRuntime.getIterator(users), $__2; !($__2 = $__1.next()).done;) {
        var user = $__2.value;
        {
          this.addUser(user);
        }
      }
    },
    onMessage: function(event, message) {
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
    },
    onMessageRemove: function(event, message) {},
    addRecentMessages: function() {
      for (var $__2 = $traceurRuntime.getIterator(window.recent), $__1; !($__1 = $__2.next()).done;) {
        var message = $__1.value;
        {
          if (this.getUser(message.user.id) === void 0) {
            this.addUser(message.user);
          }
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
      new UserProfileView(user).remove();
    },
    onPopoverClick: function(event) {
      event.stopPropagation();
      var button = $(event.target);
      var id = button.attr('data-popover');
      var popover = Popover.create(id, button);
      popover.toggle();
    },
    onProfileClick: function(event) {
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
    },
    addMessage: function(messageView) {
      var room = arguments[1] !== (void 0) ? arguments[1]: 'main';
      var chat = this.getChatRoom(room);
      chat.append(messageView.render());
      window.tabs.increaseCounter(room);
      this.scroll.down();
    },
    getUser: function(id) {
      return this.users[id];
    },
    isUserExist: function(id) {
      return this.users[id] !== void 0;
    },
    addUser: function(user) {
      this.users[user.id] = user;
    },
    getChatRoom: function(room) {
      if (!this.dom.chat[room]) {
        this.dom.board.append(new ChatBoardView(room).render());
        this.dom.chat[room] = $('#chat-' + room);
      }
      return this.dom.chat[room];
    },
    onUsernameClick: function(event) {
      var name = $(event.target).attr('data-user-name');
      this.dom.textarea.insertAtCaret(' ' + name + ' ');
    },
    onError: function(event, error) {
      notify.error(tr(error));
    }
  }, {});
  return $Application;
}();

//@ sourceMappingURL=app.map