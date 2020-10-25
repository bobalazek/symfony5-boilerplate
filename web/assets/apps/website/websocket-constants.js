// TODO: keep in sync with the one from /ws/src/constants.js
// The reason we can't simply include that one is,
// because we only mount the /web folder to the container.
export const WS_EVENT_READY = 'ready';
export const WS_EVENT_PING = 'ping';
export const WS_EVENT_PONG = 'pong';
export const WS_EVENT_CHANNEL_SUBSCRIBE = 'channel_subscribe';
export const WS_EVENT_CHANNEL_UNSUBSCRIBE = 'channel_unsubscribe';
export const WS_EVENT_CHANNEL_SUBSCRIBE_SUCCESS = 'channel_subscribe:success';
export const WS_EVENT_CHANNEL_UNSUBSCRIBE_SUCCESS = 'channel_unsubscribe:success';
