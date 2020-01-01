import { Room, EntityMap } from 'colyseus';
import * as uuid from 'uuid';

import { DEBUG, GAME_SERVER_UPDATE_RATE } from '../../Config';

export class LobbyRoom extends Room<LobbyRoomState> {

    public showLogs: boolean = DEBUG;

    public maxClients: number = 16;
    public patchRate: number = 1000 / GAME_SERVER_UPDATE_RATE;

    public onInit (options) {
        if (this.showLogs) {
            console.log('Lobby created.');
            console.log(options);
        }

        this.setState(new LobbyRoomState());
    }

    public onJoin (client) {
        this.state.addPlayer(client);
    }

    public onLeave (client) {
        this.state.removePlayer(client);
    }

    public onMessage (client, data) {
        if (this.showLogs) {
            console.log(`LobbyRoom received message from ${ client.sessionId }:`);
            console.log(data);
        }

        if (data.action === 'chat:messages:new') {
            this.state.addChatMessage(client, data.detail);
        } else if (data.action === 'entity:transform:update') {
            this.state.updateEntityTransform(client, data.detail);
        }
    }

    public onDispose () {
        if (this.showLogs) {
            console.log('Lobby disposed.');
        }
    }

}

export class LobbyRoomState {
    public chatMessages: Array<LobbyRoomChatMessage> = [];
    public actionLogs: Array<LobbyRoomActionLog> = [];
    public players: EntityMap<LobbyRoomPlayer> = {};
    public entities: EntityMap<LobbyRoomEntity> = {};

    public addPlayer(client) {
        let playerName = 'Guest ' + client.sessionId; // TODO: lookup the player name inside a database or wherever
        this.players[client.sessionId] = new LobbyRoomPlayer(client, playerName);
        this.actionLogs.push(
            new LobbyRoomActionLog(client, 'join', `${ client.sessionId } joined.`)
        );
    }

    public removePlayer(client) {
        delete this.players[client.sessionId];
        this.actionLogs.push(
            new LobbyRoomActionLog(client, 'leave', `${ client.sessionId } left.`)
        );
        for (let entityKey in this.entities) {
            if (this.entities[entityKey].client.sessionId === client.sessionId) {
                delete this.entities[entityKey];
            }
        }
    }

    public addChatMessage(client, detail) {
        this.chatMessages.push({
            id: uuid.v4(),
            sender: this.players[client.sessionId].name,
            text: detail.text,
        });
    }

    public updateEntityTransform(client, detail) {
        if (typeof this.entities[detail.id] === 'undefined') {
            this.entities[detail.id] = new LobbyRoomEntity(client, detail.id, detail.transformMatrix);
        } else {
            this.entities[detail.id].transformMatrix = detail.transformMatrix;
        }
    }
}

export class LobbyRoomChatMessage {
    public id: string;
    public sender: string;
    public text: string;

    constructor(id: string, sender: string, text: string) {
        this.id = id;
        this.sender = sender;
        this.text = text;
    }
}

export class LobbyRoomActionLog {
    public client: any;
    public action: string;
    public message: string;

    constructor(client: any, action: string, message: string) {
        this.client = client;
        this.action = action;
        this.message = message;
    }
}

export class LobbyRoomPlayer {
    public client: any;
    public name: string;

    constructor(client: any, name: string) {
        this.client = client;
        this.name = name;
    }
}

export class LobbyRoomEntity {
    public client: any;
    public id: string;
    public transformMatrix: string;

    constructor(client: any, id: string, transformMatrix: string) {
        this.client = client;
        this.id = id;
        this.transformMatrix = transformMatrix;
    }
}
