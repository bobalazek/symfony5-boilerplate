const fs = require('fs');
const http = require('http');
//const https = require('https');
const { v4: uuidv4 } = require('uuid');
const WebSocket = require('ws');

const server = http.createServer();

/*
const server = https.createServer({
    cert: fs.readFileSync('/path/to/cert.crt'),
    key: fs.readFileSync('/path/t/key.key'),
});
*/

const wss = new WebSocket.Server({ server });

let clientChannelsMap = {};
let channelClientMap = {};

// Listeners
wss.on('connection', (client) => {
    client.id = uuidv4();
    client.isAlive = true;

    client.on('message', (data) => {
        if (data.event === 'channel_subscribe') {
            clientSubscribeToChannel(client, channel);
        }
    });

    client.on('pong', () => {
        client.isAlive = true;
    });
});

wss.on('close', (client) => {
    clientUnsubscribeFromChannel(client, '*');

    client.close();
});

// Functions
function clientSubscribeToChannel(client, channel) {
    if (typeof clientChannelsMap[client.id] === 'undefined') {
        clientChannelsMap[client.id] = [];
    }
    clientChannelsMap[client.id].push(data.data.channel);

    if (typeof channelClientMap[data.data.channel] === 'undefined') {
        channelClientMap[data.data.channel] = [];
    }
    channelClientMap[data.data.channel].push(client.id);
}

function clientUnsubscribeFromChannel(client, channel) {
    const channels = channel === '*'
        ? clientChannelsMap[client.id]
        : [channel];
    for (let i = 0; i < channels.length; i++) {
        const channelClients = channelClientMap[channels[i]];
        for (let j = 0; j < channelClients.length; ++j) {
            if (channelClients[j] !== client.id) {
                continue;
            }

            channelClientMap[channels[i]].splice(j, 1);
        }
    }

    if (channel === '*') {
        clientChannelsMap[client.id] = [];
    } else {
        const channelIndex = clientChannelsMap[client.id].indexOf(channel);
        if (channelIndex !== -1) {
            clientChannelsMap[client.id].splice(channelIndex, 1);
        }
    }
}

// Listen
server.listen(8080, function() {
    console.log(`Listening on port ${server.address().port} ...`);
});
