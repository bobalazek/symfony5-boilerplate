const http = require('http');
const WebSocket = require('ws');
const {
    createServer,
    onConnection,
    onClose,
    startClientMapsGC,
} = require('./functions');

const server = http.createServer(createServer);
const wss = new WebSocket.Server({ server });

wss.on('connection', onConnection);
wss.on('close', onClose);

module.exports = {
    start: () => {
        server.listen(8080, function () {
            console.log(`Listening on port ${server.address().port} ...`);
        });

        startClientMapsGC();
    },
};
