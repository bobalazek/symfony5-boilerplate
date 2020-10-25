import {
  WS_EVENT_CHANNEL_SUBSCRIBE,
  WS_EVENT_CHANNEL_UNSUBSCRIBE,
} from '../../../../ws/src/constants';

class AppWebSocket {
  constructor(url, options) {
    this.socket = new WebSocket(url);

    this.debug = options && options.debug === true;
    this.handlers = {};
    this.channelHandlers = {};

    this.socket.onopen = (e) => {
      if (this.debug) {
        console.log('Socket open.', e);
      }

      this.startPingTimeout();
    };

    this.socket.onmessage = (e) => {
      if (this.debug) {
        console.log('Socket message.', e);
      }

      const data = JSON.parse(e.data);

      console.log(data);

      // TODO

      this.startPingTimeout();
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

      clearTimeout(this.pingTimeout);
    };
  }

  startPingTimeout() {
    clearTimeout(this.pingTimeout);

    this.pingTimeout = setTimeout(() => {
      this.socket.close();
    }, 30000 + 1000);
  }

  send(data) {
    if (this.socket.readyState !== WebSocket.OPEN) {
      setTimeout(() => {
        this.socket.send(JSON.stringify(data));
      }, 100);

      return;
    }

    this.socket.send(data);
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
      data: {
        channel: channel,
      },
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
      data: {
        channel: channel,
      },
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

module.exports = AppWebSocket;
