import * as BABYLON from 'babylonjs';
import  * as Colyseus from 'colyseus.js';

import { GameManager } from '../Core/GameManager';
import { AbstractScene } from './Scene';
import { NetworkSerializer } from '../Network/NetworkSerializer';
import { NetworkConstants } from '../Network/NetworkConstants';

export abstract class AbstractNetworkScene extends AbstractScene {
  public networkHost: string;
  public networkPort: number;
  public networkClient: Colyseus.Client;
  public networkRoom: Colyseus.Room;
  public networkInterpolationSmooting: number = 0.2; // value between 0.1 to 1
  public networkInterpolationLastUpdateTolerance: number = 1000; // in milliseconds

  prepareNetworkClient() {
    if (!this.networkHost && !this.networkPort) {
      throw new Error(
        'A networked room requires you to have `networkHost` and `networkPort` set in your class.'
      );
    }

    this.networkClient = new Colyseus.Client(
      'ws://' + this.networkHost + ':' + this.networkPort
    );
  }

  prepareNetworkClientAndJoinRoom(roomName: string, roomOptions = {}): Promise<any> {
    this.prepareNetworkClient();

    return new Promise((resolve, reject) => {
      this.networkClient.joinOrCreate(roomName, roomOptions).then(room => {
        this.networkRoom = room;

        resolve(room);
      }).catch(e => {
        console.error(e);

        reject(e);
      });
    });
  }

  prepareNetworkSync() {
    GameManager.scene.onBeforeRenderObservable.add(() => {
      const now = (new Date()).getTime();
      const meshes = GameManager.scene.meshes; // TODO: optimize
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
          mesh.metadata.network.clientLastUpdate = now;

          mesh.position = BABYLON.Vector3.Lerp(
            mesh.position,
            mesh.metadata.serverData.position,
            this.networkInterpolationSmooting
          );

          const serverDataRotation = mesh.metadata.serverData.rotation;
          const rotationQuaternion = BABYLON.Quaternion.RotationYawPitchRoll(
            serverDataRotation.y,
            serverDataRotation.x,
            serverDataRotation.z
          );

          mesh.rotationQuaternion = BABYLON.Quaternion.Slerp(
            mesh.rotationQuaternion,
            rotationQuaternion,
            this.networkInterpolationSmooting
          );
        }
      }
    });
  }

  networkReplicate(transformNode: BABYLON.TransformNode, updateFrequency: number = 100) {
    if (!transformNode.metadata) {
      transformNode.metadata = {}
    }

    transformNode.metadata.network = {
      serverReplicate: true,
      serverData: null,
      serverLastUpdate: null,
      clientLastUpdate: null,
    };

    let lastTransformNodeMatrix = null;
    return setInterval(() => {
      const transformMatrix = NetworkSerializer.serializeTransformNode(transformNode);
      if (
        this.networkRoom &&
        lastTransformNodeMatrix !== transformMatrix
      ) {
        this.networkRoom.send([
          NetworkConstants.TRANSFORM_MOVEMENT_UPDATE,
          [transformNode.id, transformMatrix]
        ]);
        lastTransformNodeMatrix = transformMatrix;
      }
    }, updateFrequency);
  }
}
