const url = require('url');
const { v4: uuidv4 } = require('uuid');
const WebSocket = require('ws');
const {
    WS_EVENT_READY,
    WS_EVENT_PING,
    WS_EVENT_PONG,
    WS_EVENT_MESSAGE,
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
    const query = parsedRequest.query;
    const body = await getRequestBody(request);

    let statusCode = 404;
    let responseBody = {
        success: false,
        error: {
            message: 'Route does not exists',
        },
    };

    // Routes
    try {
        if (
            request.method === 'POST' &&
            pathname === '/messages'
        ) {
            if (query.server_token !== WS_SERVER_TOKEN) {
                throw new Error('The server_token is invalid.');
            }

            if (!query.channel) {
                throw new Error('You need to specify a channel.');
            }

            let bodyData;
            try {
                bodyData = JSON.parse(body);
            } catch (e) {
                throw new Error('The request body need to be a valid JSON object.');
            }

            const result = dispatchMessageToClientsOnChannel(query.channel, bodyData);

            statusCode = 200;
            responseBody = {
                success: true,
                data: {
                    clients_notified_count: result.clientIds.length,
                },
            };
        }
    } catch (error) {
        statusCode = 500;
        responseBody = {
            success: false,
            error: {
                message: error.message,
            },
        };
    }

    response.writeHead(statusCode, {
        'Content-Type': 'application/json',
    });
    response.end(JSON.stringify(responseBody));
}

async function getRequestBody(request) {
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

        let parsedData;
        let event;
        let channel;

        try {
            parsedData = JSON.parse(data);
            event = parsedData.event;
            channel = parsedData.channel;
        } catch (e) {
            return;
        }

        switch (event) {
            case WS_EVENT_PING:
                clientPong(client, parsedData.data);
                break;
            case WS_EVENT_PONG:
                clientPing(client, parsedData.data);
                break;
            case WS_EVENT_CHANNEL_SUBSCRIBE:
                clientSubscribeToChannel(client, channel);
                break;
            case WS_EVENT_CHANNEL_UNSUBSCRIBE:
                clientUnsubscribeFromChannel(client, channel);
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

function clientPing(client, data) {
    sendToClient(client, {
        event: WS_EVENT_PING,
        data,
    });
}

function clientPong(client, data) {
    sendToClient(client, {
        event: WS_EVENT_PONG,
        data,
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
    let client = null;

    global.wss.clients.forEach((singleClient) => {
        if (clientId === singleClient.id) {
            client = singleClient;
        }
    });

    return client;
}

function dispatchMessageToClientsOnChannel(channel, data) {
    const channelClients = channelClientMap[channel];
    if (!channelClients) {
        return {
            channelExists: false,
            clientIds: [],
        };
    }

    let clientIds = [];

    for (let i = 0; i < channelClients.length; ++i) {
        const client = getClientById(channelClients[i]);
        if (!client) {
            continue;
        }

        sendToClient(client, {
            event: WS_EVENT_MESSAGE,
            channel,
            data,
        });

        clientIds.push(client.id);
    }

    return {
        channelExists: true,
        clientIds,
    };;
}

function startClientMapsGC() {
    // TODO: do some kind of garbage collection of disconnected clients
    // from the clientChannelsMap & channelClientMap maps.
    setInterval(() => {
        // TODO
    }, 60000);
}

module.exports = {
    createServer,
    onConnection,
    onClose,
    startClientMapsGC,
};
