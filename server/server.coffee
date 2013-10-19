config  = require('./config.json')
express = require('express')
app     = express()
server  = require('http').createServer(app)
io      = require('socket.io').listen(server)
crypto  = require('crypto')
mongo   = require('mongojs');

###
  Global variables
###
db = mongo.connect(config.database, ['keys']);
log = io.log

###
  Clients list.
  namespace/user_id => socket
###
clients = {}

###
  Socket.io configuration
###
io.configure 'production', ->
  io.enable 'browser client minification'
  # send minified client
  io.enable 'browser client etag'
  # apply etag caching logic based on version number
  io.enable 'browser client gzip'
  # gzip the file
  io.set 'log level', 1
  # reduce logging

  # enable all transports (optional if you want flashsocket support, please note that some hosting
  # providers do not allow you to create servers that listen on a port different than 80 or their
  # default port)
  io.set 'transports', ['websocket', 'flashsocket', 'htmlfile', 'xhr-polling', 'jsonp-polling']

###
  Global configuration
###
io.configure ->
  io.set 'authorization', (handshakeData, callback) ->
    handshakeData.namespace = handshakeData.query['namespace']
    callback null, true

###
  Cross domain requests
###
app.use (req, res, next) ->
  res.header("Access-Control-Allow-Origin", "*")
  res.header("Access-Control-Allow-Headers", "X-Requested-With")
  next()

###
  Body parser
###
app.use express.bodyParser()

###
  Start server listening on port.
###
server.listen config.server.port

###
  Check status
###
app.get '/status', (req, res) ->
  res.send 'All systems operational'

###
  Check JSON function
###
expect = (json, pattern, throws, current = []) ->
  condition = false
  for own key, value of pattern
    at = current.concat [key]
    if typeof value is 'boolean'
      condition = if value is true then json[key]? else not json[key]?
      if not condition and throws
        throw (if value is true then 'expected' else 'unexpected') + ' ' + at.join(' ')
    else
      if json[key]?
        condition = expect json[key], value, throws, at
      else
        condition = false
        if throws
          throw 'expected ' + at.join(' ')
  return condition

###
  Message sent.
###
app.post '/send', (req, res) ->
  json = new Buffer(req.body.encode, 'base64').toString('utf8').substring(0, 3000)

  try
    body = JSON.parse(json)
  catch error
    return res.json error: 'json error'

  check body, (error, data, doc) =>
    # Check if any error
    return res.json error: error if data is null

    try expect data, {room: yes, user: {id: yes}}, throws = yes
    catch error then return res.json error: error

    # Get user socket
    socket = clients[doc.namespace + '/' + data.user.id]

    # Check if socket exist
    return res.json error: 'no socket' if not socket?

    # Send message
    socket.broadcast.to(data.room).emit 'message', data
    socket.emit 'message', data

    # Say all right
    res.json
      error: no

###
  Get user from socket
###
getUserFrom = (socket, callback) ->
  socket.get 'user', (error, user) ->
    if user and error is null
      callback user
    else
      socket.emit 'reconnect'

###
  Dynamic namespaces for socket.io
###
io.sockets.on 'connection', (socket) ->
  namespace = '/' + socket.handshake.namespace.toLowerCase()

  return if io.namespaces[namespace]

  ## Start dynamic
  io.of(namespace).on 'connection', (socket) ->

    ## On user login
    socket.on 'login', (body) ->
      # Change body IP on our IP to check it.
      body.ip = socket.handshake.address.address if body.ip?

      # Check body
      check body, (error, data) ->
        # If any error - return
        return socket.emit 'error', error if error isnt null

        # Check json data pattern
        try expect data, {user: {id: yes}}, throws = yes
        catch error then return socket.emit 'error', error

        # Emit over client with same id what new client with same id connected
        if ocs = clients[namespace + '/' + data.user.id]
          ocs.emit 'error', 'over client connected'

        # Save to clients list
        clients[namespace + '/' + data.user.id] = socket

        # Join user personal room
        socket.join 'private-' + data.user.id

        # Save user to socket
        socket.set 'user', data.user

        # Synchronize new user
        usersList = []
        io.of(namespace).clients().forEach (client) -> getUserFrom client, (user) -> usersList.push user
        socket.emit 'synchronize', usersList

        # Send user ok
        socket.emit 'login_success'

    ## On user join
    socket.on 'join', (room) ->
      getUserFrom socket, (user) ->
        # Join room
        socket.join room

        # Send online users about new joined user
        log.debug namespace + '/' + room + ': join ' + user.name
        io.of(namespace).in(room).emit 'user_join', user

    ## On user disconnect
    socket.on 'disconnect', () ->
      getUserFrom socket, (user) ->
        delete clients[namespace + '/' + user.id]
        io.of(namespace).emit 'user_leave', user



###
   SHA1 function
###
sha1 = (input) ->
  crypto.createHash('sha1').update(input, 'utf8').digest('hex')

###
  Stringify function
###
stringify = (data) ->
  if typeof data is 'object'
    '[' + (key + ':' + stringify(value) for key, value of data).join(',') + ']'
  else
    data.toString()

###
  Doc Class
###
class Doc
  constructor: (i) ->
    @domain    = i.domain    ? 'nodomain'
    @namespace = i.namespace ? '/' + @domain
    @key       = i.key       ? ''
    @maxOnline = i.maxOnline ? 5

###
  data may contain next:
    hash - hash of all staff
    domain - domain for finding key

  callback may be a function (error, data, doc)
###
check = (data, callback) ->
  # Check if hash persist
  if data._hash is null
    return callback 'no hash', null
  userHash = data._hash
  delete data._hash

  db.keys.findOne domain: data._domain, (error, json) ->
    # Check if we find key
    if json is null or error isnt null
      return callback 'key not found', null, null

    # Create doc
    doc = new Doc json

    # Add secret key from DB
    data._key = doc.key

    # Create check hash from data
    checkHash = sha1 stringify data

    # Delete secret key from and domain too.
    delete data._key
    delete data._domain

    # Check hash
    if userHash == checkHash
      callback null, data, doc
    else
      callback 'hash not match', null, null