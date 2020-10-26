const url = require('url');
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

const WS_SERVER_TOKEN = process.env.WS_SERVER_TOKEN;

let clientChannelsMap = {};
let channelClientMap = {};

async function createServer(request, response) {
    const parsedRequest = url.parse(request.url, true);
    const pathname = parsedRequest.pathname;
    const query = parsedRequest.pathname;
    const body = await getRequestBody(request);

    let statusCode = 404;
    let responseBody = {
        success: false,
        error: {
            message: 'Route does not exists',
        },
    };

    if (
        request.method === 'POST' &&
        pathname === '/messages'
    ) {
        statusCode = 200;
        responseBody = {
            success: true,
        };
    }

    response.writeHead(statusCode, {
        'Content-Type': 'application/json',
    });
    response.end(JSON.stringify(responseBody));
}

function getRequestBody(request) {
    return new Promise((resolve, reject) => {
        let body = [];
        request.on('error', (err) => {
            console.error(err);
        }).on('data', (chunk) => {
            body.push(chunk);
        }).on('end', () => {
            body = Buffer.concat(body).toString();

            resolve(body);
        });
    });
}

function onConnection(client) {
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
}

function onClose(client) {
    clientUnsubscribeFromChannel(client, '*');

    client.close();
}

function clientReady(client) {
    sendToClient(client, {
        event: WS_EVENT_READY,
    });
}

function clientPing(client) {
    sendToClient(client, {
        event: WS_EVENT_PING,
    });
}

function clientPong(client) {
    sendToClient(client, {
        event: WS_EVENT_PONG,
    });
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

    sendToClient(client, {
        event: WS_EVENT_CHANNEL_SUBSCRIBE_SUCCESS,
        channel,
    });
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

    sendToClient(client, {
        event: WS_EVENT_CHANNEL_UNSUBSCRIBE_SUCCESS,
        channel,
    });
}

function sendToClient(client, data, retry = 0) {
    if (client.readyState !== WebSocket.OPEN) {
        if (retry <= 5) {
            setTimeout(() => {
                sendToClient(client, data, retry + 1);
            }, 200);
        }

        return;
    }

    client.send(JSON.stringify(data));
}

function getClientById(clientId) {
    for (let i = 0; i < wss.clients.length; i++) {
        if (clientId === wss.clients[i].id) {
            return wss.clients[i];
        }
    }

    return null;
}

function dispatchMessageToClientsOnChannel(channel, data) {
    const channelClients = channelClientMap[channel];
    if (channelClients) {
        return;
    }

    for (let i = 0; i < channelClients.length; ++i) {
        const client = getClientById(channelClients[i]);
        if (!client) {
            continue;
        }

        sendToClient(client, {
            event: WS_EVENT_CHANNEL_UNSUBSCRIBE_SUCCESS,
            channel,
            data,
        });
    }
}

function startClientMapsGC() {
    // TODO: do some kind of garbage collection of disconnected clients
    // from the clientChannelsMap & channelClientMap maps.
    setInterval(() => {
        // TODO
    }, 60000);
}

module.exports = {
    createServer: createServer,
    onConnection: onConnection,
    onClose: onClose,
    startClientMapsGC: startClientMapsGC,
};
