const fs = require('fs');
const http = require('http');
const https = require('https');
const WebSocket = require('ws');

const server = http.createServer(); // https.createServer();
const wss = new WebSocket.Server({ server });

// Connect
wss.on('connection', onConnection);

// Functions
function onConnection(ws) {
    ws.on('message', onMessage);
}

function onMessage(data) {
    wss.clients.forEach((client) => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(data);
        }
    });
}

// Listen
server.listen(80);
