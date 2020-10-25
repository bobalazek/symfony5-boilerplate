const http = require('http');
const { v4: uuidv4 } = require('uuid');
const WebSocket = require('ws');
const {
    WS_EVENT_READY,
    WS_EVENT_PING,
    WS_EVENT_PONG,
    WS_EVENT_CHANNEL_SUBSCRIBE,
    WS_EVENT_CHANNEL_UNSUBSCRIBE,
    WS_EVENT_CHANNEL_SUBSCRIBE_SUCCESS,
    WS_EVENT_CHANNEL_UNSUBSCRIBE_SUCCESS,
} = require('./constants');

const server = http.createServer();
const wss = new WebSocket.Server({ server });

let clientChannelsMap = {};
let channelClientMap = {};

// Listeners
wss.on('connection', (client) => {
    client.id = uuidv4();
    client.isAlive = true;
    client.lastActiveAt = Date.now();

    clientReady(client);

    client.on('message', (data) => {
        client.lastActiveAt = Date.now();

        const parsedData = JSON.parse(data);
        const messageEvent = parsedData.event;
        const messageChannel = parsedData.channel;

        switch (messageEvent) {
            case WS_EVENT_PING:
                clientPong(client);
                break;
            case WS_EVENT_PONG:
                clientPing(client);
                break;
            case WS_EVENT_CHANNEL_SUBSCRIBE:
                clientSubscribeToChannel(client, messageChannel);
                break;
            case WS_EVENT_CHANNEL_UNSUBSCRIBE:
                clientUnsubscribeFromChannel(client, messageChannel);
                break;
        }
    });
});

wss.on('close', (client) => {
    clientUnsubscribeFromChannel(client, '*');

    client.close();
});

// Functions
function clientReady(client) {
    client.send(JSON.stringify({
        event: WS_EVENT_READY,
    }));
}

function clientPing(client) {
    client.send(JSON.stringify({
        event: WS_EVENT_PING,
    }));
}

function clientPong(client) {
    client.send(JSON.stringify({
        event: WS_EVENT_PONG,
    }));
}

function clientSubscribeToChannel(client, channel) {
    if (typeof clientChannelsMap[client.id] === 'undefined') {
        clientChannelsMap[client.id] = [];
    }
    clientChannelsMap[client.id].push(channel);

    if (typeof channelClientMap[channel] === 'undefined') {
        channelClientMap[channel] = [];
    }
    channelClientMap[channel].push(client.id);

    client.send(JSON.stringify({
        event: WS_EVENT_CHANNEL_SUBSCRIBE_SUCCESS,
        channel,
    }));
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

    client.send(JSON.stringify({
        event: WS_EVENT_CHANNEL_UNSUBSCRIBE_SUCCESS,
        channel,
    }));
}

// Listen
server.listen(8080, function() {
    console.log(`Listening on port ${server.address().port} ...`);
});
