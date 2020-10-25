'use strict';

// TODO: keep in sync with the one from /ws/src/constants.js
// The reason we can't simply include that one is,
// because we only mount the /web folder to the container.
module.exports = {
  WS_EVENT_READY: 'ready',
  WS_EVENT_PING: 'ping',
  WS_EVENT_PONG: 'pong',
  WS_EVENT_CHANNEL_SUBSCRIBE: 'channel_subscribe',
  WS_EVENT_CHANNEL_UNSUBSCRIBE: 'channel_unsubscribe',
  WS_EVENT_CHANNEL_SUBSCRIBE_SUCCESS: 'channel_subscribe:success',
  WS_EVENT_CHANNEL_UNSUBSCRIBE_SUCCESS: 'channel_unsubscribe:success',
};
