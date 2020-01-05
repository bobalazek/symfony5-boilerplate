import * as BABYLON from 'babylonjs';
import  * as Colyseus from 'colyseus.js';

import {
  GAME_SERVER_HOST,
  GAME_SERVER_PORT,
} from '../Config';

import {
  GameManager,
  SceneInterface,
} from '../Core/GameManager';

export class DefaultScene implements SceneInterface {
  load() {
    GameManager.engine.displayLoadingUI();

    this.prepareClient();

    GameManager.setScene(
      this.prepareScene()
    );

    GameManager.engine.hideLoadingUI();
  }

  prepareScene(): BABYLON.Scene {
    let scene = new BABYLON.Scene(GameManager.engine);

    scene.createDefaultCameraOrLight(true, true, true);
    scene.createDefaultEnvironment();

    let camera = <BABYLON.ArcRotateCamera>scene.activeCamera;
    camera.alpha = Math.PI / 3;
    camera.beta = Math.PI / 3;
    camera.radius = 5;

    // Create a box
    BABYLON.MeshBuilder.CreateBox('box', {});

    return scene;
  }

  prepareClient() {
    let client = new Colyseus.Client(
      'ws://' + GAME_SERVER_HOST + ':' + GAME_SERVER_PORT
    );

    client.joinOrCreate('lobby', {}).then(room => {
      console.log(room);
    }).catch(e => {
      console.error(e);
    });
  }
}
