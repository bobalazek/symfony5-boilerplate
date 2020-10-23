const fs = require('fs');
const http = require('http');
const https = require('https');
const WebSocket = require('ws');

const server = http.createServer(); // https.createServer();

const wss = new WebSocket.Server({ server });

// Connect
wss.on('connection', onConnection);
wss.on('close', onClose);

const interval = setInterval(ping, 30000);

// Functions
function onConnection(ws) {
    ws.isAlive = true;

    ws.on('message', onMessage);
}

function onClose() {
    clearInterval(interval);
}

function onMessage(data) {
    wss.clients.forEach((ws) => {
        if (ws.readyState !== WebSocket.OPEN) {
            return;
        }

        ws.send(data);
    });
}

function ping() {
    wss.clients.forEach((ws) => {
        if (ws.isAlive === false) {
            return ws.terminate();
        }

        ws.isAlive = false;
        ws.ping(noop);
    });
}

function noop() {}

// Listen
server.listen(80);
