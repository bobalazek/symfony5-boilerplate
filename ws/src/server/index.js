const http = require('http');
const WebSocket = require('ws');
const {
    createServer,
    onConnection,
    onClose,
    startClientMapsGC,
} = require('./functions');

// TODO: find a better solutions for the globals ...
global.server = http.createServer(createServer);
global.wss = new WebSocket.Server({ server });

global.wss.on('connection', onConnection);
global.wss.on('close', onClose);

module.exports = {
    start: () => {
        global.server.listen(8080, function () {
            console.log(`Listening on port ${server.address().port} ...`);
        });

        startClientMapsGC();
    },
};
