import {
  WS_EVENT_PING,
  WS_EVENT_CHANNEL_SUBSCRIBE,
  WS_EVENT_CHANNEL_UNSUBSCRIBE,
  WS_EVENT_MESSAGE,
} from './websocket-constants';

export default class AppWebSocket {
  constructor(url, options) {
    this.socket = new WebSocket(url);

    this.debug = options && options.debug === true;
    this.handlers = {};
    this.channelHandlers = {};

    this.socket.onopen = (e) => {
      if (this.debug) {
        console.log('Socket open.', e);
      }

      this.resetConnectionTimeout();
      this.startPing();
    };

    this.socket.onmessage = (e) => {
      if (this.debug) {
        console.log('Socket message.', e);
      }

      const parsedData = JSON.parse(e.data);
      switch (parsedData.event) {
        case WS_EVENT_MESSAGE:
          this.onMessage(parsedData);
          break;
      }

      this.resetConnectionTimeout();
    };

    this.socket.onerror = (error) => {
      if (this.debug) {
        console.log('Socket error.', error);
      }
    };

    this.socket.onclose = (e) => {
      if (this.debug) {
        console.log('Socket close.', e);
      }

      this.onClose();
    };
  }

  resetConnectionTimeout() {
    clearTimeout(this.connectionTimeout);

    this.connectionTimeout = setTimeout(() => {
      this.socket.close();
    }, 30000 + 1000);
  }

  startPing() {
    this.pingInterval = setInterval(() => {
      this.send({
        event: WS_EVENT_PING,
        data: {
          id: Math.random().toString(36).slice(2),
        },
      });
    }, 10000);
  }

  onMessage(data) {
    if (data && data.event) {
      this.trigger(data.event, data);
    }

    if (data && data.channel) {
      this.triggerChannel(data.channel, data);
    }
  }

  onClose() {
    clearInterval(this.pingInterval);
    clearTimeout(this.connectionTimeout);
  }

  send(data) {
    if (this.socket.readyState !== WebSocket.OPEN) {
      setTimeout(() => {
        this.send(data);
      }, 100);

      return;
    }

    this.socket.send(JSON.stringify(data));
  }

  on(eventName, callback) {
    if (typeof this.handlers[eventName] === 'undefined') {
      this.handlers[eventName] = [];
    }

    this.handlers[eventName].push(callback);
  }

  off(eventName, callback) {
    this.handlers[eventName] = this.handlers[eventName].filter((item) => {
      if (item !== callback) {
        return item;
      }
    });
  }

  trigger(eventName, data) {
    if (typeof this.handlers[eventName] === 'undefined') {
      return;
    }

    this.handlers[eventName].forEach((item) => {
      item.call(this, data);
    });
  }

  onChannel(channel, callback) {
    if (typeof this.channelHandlers[channel] === 'undefined') {
      this.channelHandlers[channel] = [];
    }

    this.channelHandlers[channel].push(callback);

    this.send({
      event: WS_EVENT_CHANNEL_SUBSCRIBE,
      channel,
    });
  }

  offChannel(channel, callback) {
    this.channelHandlers[channel] = this.channelHandlers[channel].filter((item) => {
      if (item !== callback) {
        return item;
      }
    });

    this.send({
      event: WS_EVENT_CHANNEL_UNSUBSCRIBE,
      channel,
    });
  }

  triggerChannel(channel, data) {
    if (typeof this.channelHandlers[channel] === 'undefined') {
      return;
    }

    this.channelHandlers[channel].forEach((item) => {
      item.call(this, data);
    });
  }
}
