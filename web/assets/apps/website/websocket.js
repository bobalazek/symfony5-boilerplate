class AppWebSocket {
  constructor(url, options) {
    this.socket = new WebSocket(url);

    this.debug = options && options.debug === true;

    this.socket.onopen = (e) => {
      if (this.debug) {
        console.log('Socket open.', e);
      }

      this.startPing();
    };

    this.socket.onmessage = (e) => {
      if (this.debug) {
        console.log('Socket message.', e);
      }

      // TODO
    };

    this.socket.onerror = (error) => {
      if (this.debug) {
        console.log('Socket error.', error);
      }

      // TODO
    };

    this.socket.onclose = (e) => {
      if (this.debug) {
        console.log('Socket close.', e);
      }

      clearTimeout(this.pingTimeout);
    };
  }

  startPing() {
    clearTimeout(this.pingTimeout);

    this.pingTimeout = setTimeout(function () {
      this.socket.close();
    }, 30000 + 1000);
  }

  send(data) {
    this.socket.send(data);
  }
};

module.exports = AppWebSocket;
