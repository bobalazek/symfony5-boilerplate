const fs = require('fs');
const http = require('http');
//const https = require('https');
const WebSocket = require('ws');

const server = http.createServer();

/*
const server = https.createServer({
    cert: fs.readFileSync('/path/to/cert.crt'),
    key: fs.readFileSync('/path/t/key.key'),
});
*/

const wss = new WebSocket.Server({ server });

// Listeners
wss.on('connection', onConnection);
wss.on('close', onClose);

const pingInterval = setInterval(ping, 30000);

// Functions
function onConnection(ws) {
    ws.isAlive = true;

    ws.on('message', onMessage);
    ws.on('pong', () => {
        ws.isAlive = true;
    });
}

function onClose() {
    clearInterval(pingInterval);
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
        if (!ws.isAlive) {
            return ws.terminate();
        }

        ws.isAlive = false;
        ws.send(noop);
    });
}

// Listen
server.listen(8080, function() {
    console.log(`Listening on port ${server.address().port} ...`);
});
