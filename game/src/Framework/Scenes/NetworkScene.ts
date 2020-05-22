import {
  TransformNode,
  Vector3,
  Quaternion,
} from 'babylonjs';
import {
  Client,
  Room,
} from 'colyseus.js';
import Cookies from 'js-cookie'

import { GameManager } from '../Core/GameManager';
import { AbstractScene } from './Scene';
import { NetworkSerializer } from '../Network/NetworkSerializer';
import { NetworkRoomConstants } from '../Network/NetworkConstants';

export abstract class AbstractNetworkScene extends AbstractScene {
  public networkHost: string;
  public networkPort: number;
  public networkClient: Client;
  public networkRoom: Room;
  public networkRoomSessionId: string;
  public readonly networkPingInterval: number = 5000; // in milliseconds
  public readonly networkInterpolationSmooting: number = 0.2; // value between 0.1 to 1
  public readonly networkInterpolationLastUpdateTolerance: number = 1000; // in milliseconds

  prepareNetworkClient() {
    if (!this.networkHost && !this.networkPort) {
      throw new Error(
        'A networked room requires you to have `networkHost` and `networkPort` set in your scene class.'
      );
    }

    this.networkClient = new Client(
      'ws://' + this.networkHost + ':' + this.networkPort
    );
  }

  prepareNetworkClientAndJoinRoom(roomName: string, roomOptions = {}): Promise<any> {
    this.prepareNetworkClient();

    return new Promise((resolve, reject) => {
      this.networkClient.joinOrCreate(roomName, roomOptions).then(room => {
        this.networkRoom = room;
        this.networkRoomSessionId = room.sessionId;

        Cookies.set('lastNetworkRoomId', room.id);
        Cookies.set('lastNetworkRoomSessionId', room.sessionId);

        resolve(room);
      }).catch(e => {
        reject(e);
      });
    });
  }

  prepareNetworkToReplicateTransformsMovement() {
    GameManager.babylonScene.onBeforeRenderObservable.add(() => {
      const now = (new Date()).getTime();
      const meshes = GameManager.babylonScene.meshes; // TODO: optimize
      for (let i = 0; i < meshes.length; i++) {
        let mesh = meshes[i];
        const meshMetadataNetwork = mesh.metadata && mesh.metadata.network
          ? mesh.metadata.network
          : false;

        if (
          meshMetadataNetwork !== false &&
          meshMetadataNetwork.serverReplicate === true &&
          meshMetadataNetwork.serverLastUpdate !== null &&
          now - meshMetadataNetwork.serverLastUpdate < this.networkInterpolationLastUpdateTolerance
        ) {
          const serverData = mesh.metadata.network.serverData;

          // Position
          mesh.position = Vector3.Lerp(
            mesh.position,
            serverData.position,
            this.networkInterpolationSmooting
          );

          // Rotation
          const rotationQuaternion = Quaternion.RotationYawPitchRoll(
            serverData.rotation.y,
            serverData.rotation.x,
            serverData.rotation.z
          );

          if (!mesh.rotationQuaternion) {
            mesh.rotationQuaternion = Quaternion.Identity();
          }

          mesh.rotationQuaternion = Quaternion.Slerp(
            mesh.rotationQuaternion,
            rotationQuaternion,
            this.networkInterpolationSmooting
          );

          mesh.metadata.network.clientLastUpdate = now;
        }
      }
    });
  }

  prepareNetworkReplicateMovementForLocalTransform(transformNode: TransformNode, updateFrequency: number = 100) {
    this.prepareTransformNodeNetworkMetadata(transformNode);

    let lastUpdate = 0;
    let lastUpdateTimeAgo = 0;
    let lastTransformNodeMatrix = null;

    GameManager.babylonScene.onAfterRenderObservable.add(() => {
      const now = (new Date()).getTime();
      lastUpdateTimeAgo += now - lastUpdate;

      if (lastUpdateTimeAgo > updateFrequency) {
        const transformMatrix = NetworkSerializer.serializeTransformNode(transformNode);
        if (
          this.networkRoom &&
          lastTransformNodeMatrix !== transformMatrix
        ) {
          this.networkRoom.send(
            NetworkRoomConstants.TRANSFORM_MOVEMENT_UPDATE,
            [transformNode.id, transformMatrix]
          );

          lastTransformNodeMatrix = transformMatrix;
          lastUpdateTimeAgo = 0;
        }
      }

      lastUpdate = now;
    });
  }

  prepareNetworkPing() {
    let pings = {};
    let lastUpdate = 0;
    let lastUpdateTimeAgo = 0;

    GameManager.babylonScene.onAfterRenderObservable.add(() => {
      const now = (new Date()).getTime();
      lastUpdateTimeAgo += now - lastUpdate;

      if (lastUpdateTimeAgo > this.networkPingInterval) {
        pings[now] = true;

        this.networkRoom.send(
          NetworkRoomConstants.PING,
          now
        );

        lastUpdateTimeAgo = 0;
      }

      lastUpdate = now;
    });

    this.networkRoom.onMessage(NetworkRoomConstants.PONG, (message) => {
      const now = (new Date()).getTime();
      this.networkRoom.send(
        NetworkRoomConstants.SET_PING,
        now - message
      );
    });
  }

  prepareTransformNodeNetworkMetadata(transformNode: TransformNode) {
    if (!transformNode.metadata) {
      transformNode.metadata = {}
    }

    if (!transformNode.metadata.network) {
      transformNode.metadata.network = {
        serverReplicate: true,
        serverData: null,
        serverLastUpdate: null,
        clientLastUpdate: null,
      };
    }
  }
}
