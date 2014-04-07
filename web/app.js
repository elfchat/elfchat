Mustache.tags = ['[', ']'];
$.fn.exist = function() {
  return $(this).length > 0;
};
function tr(message) {
  return window.lang[message] ? window.lang[message] : message;
}
function format(message) {
  var params = arguments[1] !== (void 0) ? arguments[1] : {};
  var key,
      value;
  for (key in params)
    if (params.hasOwnProperty(key)) {
      value = params[key];
      message = message.split('%' + key + '%').join(value);
    }
  return message;
}
;
var AbstractServer = function AbstractServer() {
  "use strict";
  this.connected = false;
};
($traceurRuntime.createClass)(AbstractServer, {
  connect: function() {
    "use strict";
  },
  send: function(text) {
    "use strict";
  },
  sendPrivate: function(userId, text) {
    "use strict";
  },
  onData: function(receive) {
    "use strict";
    var json = JSON.parse(receive.data);
    var type = json[0];
    var data = json[1];
    switch (type) {
      case 0:
        this.onSynchronize(data);
        break;
      case 1:
        this.onUserJoin(data);
        break;
      case 2:
        this.onUserLeave(data);
        break;
      case 3:
        this.onMessage(data);
        break;
      default:
        throw new Error('Unknown message type received from server.');
    }
  },
  onConnect: function() {
    "use strict";
    this.connected = true;
    $(window).trigger('connect');
  },
  onDisconnect: function() {
    "use strict";
    if (this.connected) {
      this.connected = false;
      $(window).trigger('disconnect');
    }
  },
  onSynchronize: function(users) {
    "use strict";
    $(window).trigger('synchronize', users);
  },
  onUserJoin: function(user) {
    "use strict";
    $(window).trigger('user_join', user);
  },
  onUserLeave: function(user) {
    "use strict";
    $(window).trigger('user_leave', user);
  },
  onMessage: function(message) {
    "use strict";
    $(window).trigger('message', message);
  },
  onError: function(error) {
    "use strict";
    console.error(error);
  }
}, {});
var WebSocketServer = function WebSocketServer(server, port) {
  "use strict";
  $traceurRuntime.superCall(this, $WebSocketServer.prototype, "constructor", []);
  this.socket = null;
  this.server = server;
  this.port = port;
  this.reconnect = null;
};
var $WebSocketServer = WebSocketServer;
($traceurRuntime.createClass)(WebSocketServer, {
  connect: function() {
    "use strict";
    var $__1 = this;
    this.socket = new WebSocket('ws://' + this.server + ':' + this.port);
    this.socket.onopen = (function() {
      $__1.onConnect();
      clearInterval($__1.reconnect);
    });
    this.socket.onclose = (function(event) {
      if (event.wasClean) {
        $__1.onDisconnect();
      } else {
        $__1.onDisconnect();
        clearInterval($__1.reconnect);
        $__1.reconnect = setInterval((function() {
          $__1.connect();
        }), 1000);
      }
    });
    this.socket.onmessage = (function(receive) {
      $__1.onData(receive);
    });
    this.onerror = (function(error) {
      $__1.onError(error.message);
    });
  },
  send: function(text) {
    "use strict";
    this.socket.send(text);
  },
  sendPrivate: function(userId, text) {
    "use strict";
  }
}, {}, AbstractServer);
function Notify() {
  this.error = function(error) {};
  this.alert = function(text) {};
  var connecting = null;
  this.connecting = {
    start: function() {},
    stop: function() {}
  };
}
var Sound = function Sound() {
  "use strict";
  this.message = this.create(window.config.sound.message);
  this.join = this.create(window.config.sound.join);
};
($traceurRuntime.createClass)(Sound, {create: function(file) {
    "use strict";
    return new Howl({urls: [file]});
  }}, {});
var Scroll = function Scroll(div) {
  "use strict";
  var _this = this;
  this.div = div;
  this.able = true;
  this.scrolling = 0;
  this.div.on('scroll', function(e) {
    if (_this.scrolling === 0) {
      return _this.able = _this.div.scrollTop() + _this.div.outerHeight() + 10 > _this.div[0].scrollHeight;
    }
  });
};
($traceurRuntime.createClass)(Scroll, {
  down: function() {
    "use strict";
    var _this = this;
    if (this.able) {
      this.scrolling += 1;
      return this.div.scrollTo('100%', 300, {onAfter: function() {
          return _this.scrolling -= 1;
        }});
    }
  },
  instantlyDown: function() {
    "use strict";
    return this.div.scrollTo('100%', 0);
  }
}, {});
var popovers = {};
var Popover = function Popover(id) {
  "use strict";
  var button = arguments[1] !== (void 0) ? arguments[1] : null;
  var box = arguments[2] !== (void 0) ? arguments[2] : '.box';
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
};
var $Popover = Popover;
($traceurRuntime.createClass)(Popover, {
  on: function(button) {
    "use strict";
    var _this = this;
    this.button = $(button);
    this.button.mousedown((function(event) {
      _this.autohide = false;
    }));
    return this;
  },
  reposition: function() {
    "use strict";
    var arrow,
        arrowPosition,
        boxSize,
        buttonOffset,
        buttonSize,
        offset,
        over,
        popover,
        popoverPosition,
        popoverSize;
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
        offset = -over + this.margin;
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
        offset = -over + this.margin;
        popoverPosition.left += offset;
        arrowPosition.left -= offset;
      }
    }
    popover.css(popoverPosition);
    arrow.css(arrowPosition);
  },
  show: function() {
    "use strict";
    this.reposition();
    this.getPopover().show();
    return this.autohide = true;
  },
  hide: function() {
    "use strict";
    return this.getPopover().hide();
  },
  toggle: function() {
    "use strict";
    this.reposition();
    this.getPopover().toggle();
    return this.autohide = true;
  },
  getPopover: function() {
    "use strict";
    return $('#' + this.id);
  },
  getArrow: function() {
    "use strict";
    return this.getPopover().find('.arrow');
  }
}, {
  create: function(id, button) {
    "use strict";
    if (popovers[id]) {
      return popovers[id].on(button);
    } else {
      return popovers[id] = new $Popover(id, button);
    }
  },
  get: function(id) {
    "use strict";
    return popovers[id];
  }
});
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
var View = function View() {
  "use strict";
};
($traceurRuntime.createClass)(View, {
  render: function() {
    "use strict";
    throw new TypeError('View class must implement render() method.');
  },
  toString: function() {
    "use strict";
    return this.render();
  }
}, {});
var UserView = function UserView(user) {
  "use strict";
  this.user = user;
};
($traceurRuntime.createClass)(UserView, {render: function() {
    "use strict";
    return template('chat/tab/user')({user: this.user});
  }}, {}, View);
var MessageView = function MessageView(message) {
  "use strict";
  this.id = message.id;
  this.time = moment(message.datetime).format('HH:mm:ss');
  this.text = this.filter(this.escape(message.text));
  this.user = message.user;
  this.spirit = false;
  if (this.text.match(/^∞/)) {
    this.text = this.text.substring(1);
    this.spirit = true;
  }
};
($traceurRuntime.createClass)(MessageView, {
  render: function() {
    "use strict";
    return template(this.spirit ? 'chat/board/spirit' : 'chat/board/message')(this);
  },
  escape: function(html) {
    "use strict";
    return html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&apos;');
  },
  filter: function(text) {
    "use strict";
    for (var $__7 = window.chat.filters[Symbol.iterator](),
        $__8; !($__8 = $__7.next()).done; ) {
      var filter = $__8.value;
      {
        text = filter.filter(text);
      }
    }
    return text;
  }
}, {}, View);
var LogView = function LogView(text) {
  "use strict";
  $traceurRuntime.superCall(this, $LogView.prototype, "constructor", [{
    id: 0,
    time: new Date(),
    user: null,
    text: text
  }]);
};
var $LogView = LogView;
($traceurRuntime.createClass)(LogView, {render: function() {
    "use strict";
    return template('chat/board/log')(this);
  }}, {}, MessageView);
var UserProfileView = function UserProfileView(user) {
  "use strict";
  this.id = 'profile-' + user.id;
  this.user = user;
};
($traceurRuntime.createClass)(UserProfileView, {
  render: function() {
    "use strict";
    return template('chat/popover/profile')(this);
  },
  remove: function() {
    "use strict";
    $('#' + this.id).remove();
  },
  exist: function() {
    "use strict";
    return $('#' + this.id).exist();
  }
}, {}, View);
var EmotionTabView = function EmotionTabView(title, emotions) {
  "use strict";
  this.title = title;
  this.emotions = emotions;
};
($traceurRuntime.createClass)(EmotionTabView, {render: function() {
    "use strict";
    return template('chat/emotion/tab')(this);
  }}, {}, View);
var EmotionImageView = function EmotionImageView(src) {
  "use strict";
  this.src = src;
};
($traceurRuntime.createClass)(EmotionImageView, {render: function() {
    "use strict";
    return template('chat/emotion/image')(this);
  }}, {}, View);
var Users = function Users() {
  "use strict";
  this.dom = {
    users: $('#users'),
    user: function(user) {
      return $('#user-' + user.id);
    }
  };
  this.users = {};
  $(window).on('synchronize', $.proxy(this.onSynchronize, this)).on('user_join', $.proxy(this.onUserJoin, this)).on('user_leave', $.proxy(this.onUserLeave, this)).on('user_update', $.proxy(this.onUserUpdate, this));
  for (var $__10 = window.recent[Symbol.iterator](),
      $__11; !($__11 = $__10.next()).done; ) {
    var message = $__11.value;
    {
      this.addUser(message.user);
    }
  }
};
($traceurRuntime.createClass)(Users, {
  onSynchronize: function(event) {
    "use strict";
    for (var users = [],
        $__12 = 1; $__12 < arguments.length; $__12++)
      users[$__12 - 1] = arguments[$__12];
    this.dom.users.html('');
    for (var $__10 = users[Symbol.iterator](),
        $__11; !($__11 = $__10.next()).done; ) {
      var user = $__11.value;
      {
        this.addUser(user);
        this.dom.users.append(new UserView(user).render());
      }
    }
  },
  onUserJoin: function(event, user) {
    "use strict";
    this.addUser(user);
    var tab = this.dom.user(user);
    var view = new UserView(user);
    if (tab.exist()) {
      tab.remove();
    }
    this.dom.users.append(view.render());
  },
  onUserLeave: function(event, user) {
    "use strict";
    var tab = this.dom.user(user);
    if (tab.exist()) {
      tab.remove();
    }
  },
  onUserUpdate: function(event, user) {
    "use strict";
    this.addUser(user);
    var tab = this.dom.user(user);
    if (tab.exist()) {
      tab.replaceWith(window.views.user(user));
    } else {
      this.dom.users.append(window.views.user(user));
    }
  },
  getUser: function(id) {
    "use strict";
    if (this.isUserExist(id)) {
      return this.users[id];
    } else {
      throw new Error('Need to load data from server.');
    }
  },
  isUserExist: function(id) {
    "use strict";
    return this.users[id] !== void 0;
  },
  addUser: function(user) {
    "use strict";
    this.users[user.id] = user;
  }
}, {});
var Emotion = function Emotion() {
  "use strict";
  this.dom = {
    popover: $('#emotions'),
    content: $('#emotions .content'),
    button: $('button[data-popover="emotions"]'),
    textarea: $('footer textarea')
  };
  this.currentTab = 0;
  if (EmotionCatalog) {
    this.tabs = new EmotionTabs(EmotionCatalog);
    this.bind();
  }
};
($traceurRuntime.createClass)(Emotion, {
  onButtonClick: function(event) {
    "use strict";
    this.render();
  },
  render: function() {
    "use strict";
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
    "use strict";
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
    "use strict";
    return this.dom.content.find(".tab:eq(" + i + ")");
  }
}, {});
var EmotionTabs = function EmotionTabs(catalog) {
  "use strict";
  this.pertab = 28;
  this.catalog = catalog;
  this.n = 0;
  this.emotions = [];
  this.max = 0;
};
($traceurRuntime.createClass)(EmotionTabs, {
  render: function() {
    "use strict";
    var html = '';
    for (var title in this.catalog)
      if (this.catalog.hasOwnProperty(title)) {
        var tab = this.catalog[title];
        for (var $__14 = tab[Symbol.iterator](),
            $__15; !($__15 = $__14.next()).done; ) {
          var emotion = $__15.value;
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
    "use strict";
    this.n = 0;
    this.emotions = [];
    this.max++;
    return new EmotionTabView(tr(title), emotions).render();
  }
}, {});
var Filter = function Filter() {
  "use strict";
};
($traceurRuntime.createClass)(Filter, {
  filter: function(html) {
    "use strict";
    return this.explode(html, {
      tag: $.proxy(this.tag, this),
      text: $.proxy(this.text, this)
    });
  },
  explode: function(html, map) {
    "use strict";
    var array,
        i,
        length,
        part,
        tag,
        text,
        _i,
        _ref,
        _ref1;
    if (map == null) {
      map = null;
    }
    array = [''];
    length = html.length;
    part = 0;
    for (i = _i = 0; 0 <= length ? _i <= length : _i >= length; i = 0 <= length ? ++_i : --_i) {
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
    tag = (_ref = map.tag) != null ? _ref : function(value) {
      return value;
    };
    text = (_ref1 = map.text) != null ? _ref1 : function(value) {
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
var BBCodeFilter = function BBCodeFilter() {
  "use strict";
  $traceurRuntime.defaultSuperCall(this, $BBCodeFilter.prototype, arguments);
};
var $BBCodeFilter = BBCodeFilter;
($traceurRuntime.createClass)(BBCodeFilter, {text: function(text) {
    "use strict";
    text = text.replace(/\[bg=([#0-9a-z]{1,20})\]((?:.(?!\[bg))*)\[\/bg\]/ig, '<span style="background-color:$1;">$2</span>');
    text = text.replace(/\[color=([#0-9a-z]{1,20})\]((?:.(?!\[color))*)\[\/color\]/ig, '<span style="color:$1;">$2</span>');
    text = text.replace(/\[b\]((?:.(?!\[b\]))*)\[\/b\]/ig, '<b>$1</b>');
    text = text.replace(/\[i\]((?:.(?!\[i\]))*)\[\/i\]/ig, '<i>$1</i>');
    text = text.replace(/\[s\]((?:.(?!\[s\]))*)\[\/s\]/ig, '<s>$1</s>');
    text = text.replace(/\[m\]((?:.(?!\[s\]))*)\[\/m\]/ig, '<marquee>$1</marquee>');
    text = text.replace(/\[quote([^\]]*)\]((?:.(?!\[quote))*)\[\/quote\]/ig, function(m, p1, p2) {
      var info,
          msg,
          name,
          quote,
          time;
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
  }}, {}, Filter);
var RestrictionFilter = function RestrictionFilter() {
  "use strict";
  this.maximumLengthOfWords = 100;
  this.maximumNumberOfLines = 20;
};
($traceurRuntime.createClass)(RestrictionFilter, {filter: function(html) {
    "use strict";
    var linesCount,
        _this = this;
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
  }}, {}, Filter);
var UriFilter = function UriFilter() {
  "use strict";
  var _ref,
      _ref1,
      _ref2;
  this.imageable = true;
  this.imageCount = 0;
  this.maxImages = 3;
  this.regex = /(https?):\/\/((?:[a-z0-9.-]|%[0-9A-F]{2}){3,})(?::(\d+))?((?:\/(?:[a-z0-9-._~!$&'()*+,;=:@]|%[0-9A-F]{2})*)*)(?:\?((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9A-F]{2})*))?(?:#((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9A-F]{2})*))?/ig;
};
($traceurRuntime.createClass)(UriFilter, {
  text: function(text) {
    "use strict";
    this.images = 0;
    return text = text.replace(this.regex, $.proxy(this.callback, this));
  },
  callback: function(uri, p1, p2, p3, p4, p5, p6, p7, p8, p9) {
    "use strict";
    var ext,
        id,
        img,
        text,
        _ref,
        _this = this;
    text = uri;
    ext = uri.match(/\.([a-z0-9]+)$/i);
    if (((_ref = ext != null ? ext[1] : void 0) === 'jpg' || _ref === 'jpeg' || _ref === 'png' || _ref === 'gif') && this.imageable && this.images++ < this.maxImages) {
      this.imageCount += 1;
      id = 'external-img-' + this.imageCount;
      text = "<img class=\"external\" id=\"" + id + "\" src=\"" + uri + "\">";
      img = new Image();
      img.src = uri;
      img.onload = function() {
        return (function(id, uri) {
          var height;
          height = $('#' + id).height();
          window.scroll.down();
        })(id, uri);
      };
    }
    return "<a href=\"" + uri + "\" target=\"_blank\">" + text + "</a>";
  }
}, {}, Filter);
var EmotionFilter = function EmotionFilter(list) {
  "use strict";
  this.list = list;
  this.max = 20;
};
($traceurRuntime.createClass)(EmotionFilter, {text: function(text) {
    "use strict";
    var _this = this;
    var count = 0;
    text = text.replace(/&apos;/g, "'");
    for (var $__17 = this.list[Symbol.iterator](),
        $__18; !($__18 = $__17.next()).done; ) {
      var row = $__18.value;
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
  }}, {}, Filter);
var Application = function Application(server) {
  "use strict";
  this.server = server;
  this.filters = [];
  this.dom = {
    chat: $('#chat'),
    textarea: $('#message'),
    body: $('body')
  };
  $(window).on('connect', $.proxy(this.onConnect, this)).on('disconnect', $.proxy(this.onDisconnect, this)).on('message', $.proxy(this.onMessage, this)).on('user_join', $.proxy(this.onUserJoin, this)).on('user_leave', $.proxy(this.onUserLeave, this)).on('error', $.proxy(this.onError, this));
  $(document).on('click.popover', '[data-popover]', $.proxy(this.onPopoverClick, this)).on('click.profile', '[data-user-id]', $.proxy(this.onProfileClick, this)).on('click.username', '[data-user-name]', $.proxy(this.onUsernameClick, this));
  $(this.dom.textarea).bind('keydown', 'return', $.proxy(this.onSend, this));
  this.filters = [new BBCodeFilter(), new UriFilter(), new EmotionFilter(EmotionList), new RestrictionFilter()];
};
($traceurRuntime.createClass)(Application, {
  run: function() {
    "use strict";
    notify.connecting.start();
    this.server.connect();
    this.addRecentMessages();
  },
  onSend: function(event) {
    "use strict";
    this.server.send(this.dom.textarea.val());
    this.dom.textarea.val('');
    event.stopPropagation();
    return false;
  },
  onConnect: function(event) {
    "use strict";
    notify.connecting.stop();
  },
  onDisconnect: function(event) {
    "use strict";
    notify.connecting.start();
  },
  onMessage: function(event, message) {
    "use strict";
    this.addMessage(message);
    window.sound.message.play();
  },
  onMessageRemove: function(event, message) {
    "use strict";
  },
  addRecentMessages: function() {
    "use strict";
    for (var $__20 = window.recent[Symbol.iterator](),
        $__21; !($__21 = $__20.next()).done; ) {
      var message = $__21.value;
      {
        this.addMessage(message);
      }
    }
    window.scroll.instantlyDown();
  },
  onUserJoin: function(event, user) {
    "use strict";
    this.addLog(format(tr('%name% joins the chat.'), {'name': user.name}));
    window.sound.join.play();
  },
  onUserLeave: function(event, user) {
    "use strict";
    this.addLog(format(tr('%name% leaves the chat.'), {'name': user.name}));
  },
  onPopoverClick: function(event) {
    "use strict";
    event.stopPropagation();
    var button = $(event.target);
    var id = button.attr('data-popover');
    var popover = Popover.create(id, button);
    popover.toggle();
  },
  onProfileClick: function(event) {
    "use strict";
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
  },
  addMessage: function(message) {
    "use strict";
    if (message !== undefined) {
      this.dom.chat.append(new MessageView(message).render());
      window.scroll.down();
    }
  },
  addLog: function(log) {
    "use strict";
    if (log !== undefined) {
      this.dom.chat.append(new LogView(log).render());
      window.scroll.down();
    }
  },
  onUsernameClick: function(event) {
    "use strict";
    var name = $(event.target).attr('data-user-name');
    this.dom.textarea.insertAtCaret(' ' + name + ' ');
  },
  onError: function(event, error) {
    "use strict";
    notify.error(tr(error));
  }
}, {});

//# sourceMappingURL=app.map
