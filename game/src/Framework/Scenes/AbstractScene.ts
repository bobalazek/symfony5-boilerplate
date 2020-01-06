import * as BABYLON from 'babylonjs';
import  * as Colyseus from 'colyseus.js';

import { GameManager } from '../Core/GameManager';
import { NetworkSerializer } from '../Network/NetworkSerializer';
import { NetworkConstants } from '../Network/NetworkConstants';
import {
  GAME_SERVER_HOST,
  GAME_SERVER_PORT,
} from '../../Config';

export interface SceneInterface {
    load: () => void;
}

export abstract class AbstractScene implements SceneInterface {
  public scene: BABYLON.Scene;

  // Network
  public networkClient: Colyseus.Client;
  public networkRoom: Colyseus.Room;
  public networkInterpolationSmooting: number = 0.2; // value between 0.1 to 1
  public networkInterpolationLastUpdateTolerance: number = 1000; // in milliseconds

  load() {
    // Show preloader
    GameManager.engine.displayLoadingUI();

    // Prepare scene
    this.prepareScene();

    // Set scene & hide preloader
    GameManager.setScene(this.scene);
    GameManager.engine.hideLoadingUI();
  }

  prepareScene() {
    this.scene = new BABYLON.Scene(GameManager.engine);
    this.scene.createDefaultCameraOrLight(true, true, true);
    this.scene.createDefaultEnvironment();

    let camera = <BABYLON.ArcRotateCamera>this.scene.activeCamera;

    camera.alpha = Math.PI / 3;
    camera.beta = Math.PI / 3;
    camera.radius = 10;
    camera.upperBetaLimit = Math.PI / 2;

    BABYLON.MeshBuilder.CreateBox('box', {});
  }

  prepareNetworkClient() {
    this.networkClient = new Colyseus.Client(
      'ws://' + GAME_SERVER_HOST + ':' + GAME_SERVER_PORT
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

  prepareNetworkReplication() {
    GameManager.scene.onBeforeRenderObservable.add(() => {
      const now = (new Date()).getTime();
      const meshes = GameManager.scene.meshes;
      for (let i = 0; i < meshes.length; i++) {
        let mesh = meshes[i];
        if (
          mesh.metadata !== null &&
          mesh.metadata.serverReplicated === true &&
          now - mesh.metadata.serverLastUpdate < this.networkInterpolationLastUpdateTolerance
        ) {
          mesh.metadata.clientLastUpdate = now;
          mesh.position = BABYLON.Vector3.Lerp(
            mesh.position,
            mesh.metadata.serverPosition,
            this.networkInterpolationSmooting
          );
          mesh.rotationQuaternion = BABYLON.Quaternion.Slerp(
            mesh.rotationQuaternion,
            mesh.metadata.serverRotation,
            this.networkInterpolationSmooting
          );
        }
      }
    });
  }

  replicatePlayer(transformNode: BABYLON.TransformNode, updateFrequency: number = 100) {
    transformNode.metadata = {
      serverReplicated: true,
      clientLastUpdate: (new Date()).getTime(),
    };

    let lastTransformNodeMatrix = null;
    return setInterval(() => {
      const transformMatrix = NetworkSerializer.serializeTransformNode(transformNode);
      if (
        this.networkRoom &&
        lastTransformNodeMatrix !== transformMatrix
      ) {
        this.networkRoom.send([
          NetworkConstants.PLAYER_TRANSFORM_UPDATE,
          transformMatrix
        ]);
        lastTransformNodeMatrix = transformMatrix;
      }
    }, updateFrequency);
  }
}
