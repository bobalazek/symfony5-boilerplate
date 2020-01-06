import * as BABYLON from 'babylonjs';
import  * as Colyseus from 'colyseus.js';

import {
  GameManager,
  SceneInterface,
} from '../Core/GameManager';
import { Serializer } from '../Network/Serializer';
import { PLAYER_TRANSFORM_UPDATE } from '../Network/Constants';
import {
  GAME_SERVER_HOST,
  GAME_SERVER_PORT,
  GAME_SERVER_TICK_RATE,
} from '../Config';

export class AbstractScene implements SceneInterface {
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

    BABYLON.MeshBuilder.CreateBox('box', {});
  }

  prepareNetworkClientAndJoinRoom(roomName: string, roomOptions = {}): Promise<any> {
    this.networkClient = new Colyseus.Client(
      'ws://' + GAME_SERVER_HOST + ':' + GAME_SERVER_PORT
    );

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

  replicatePlayer(transformNode: BABYLON.TransformNode) {
    transformNode.metadata = {
      serverReplicated: true,
      clientLastUpdate: (new Date()).getTime(),
    };

    let lastTransformNodeMatrix = null;
    setInterval(() => {
      const transformMatrix = Serializer.serializeTransformNode(transformNode);
      if (
        this.networkRoom &&
        lastTransformNodeMatrix !== transformMatrix
      ) {
        this.networkRoom.send([
          PLAYER_TRANSFORM_UPDATE,
          transformMatrix
        ]);
        lastTransformNodeMatrix = transformMatrix;
      }
    }, 1000 / GAME_SERVER_TICK_RATE);
  }
}
