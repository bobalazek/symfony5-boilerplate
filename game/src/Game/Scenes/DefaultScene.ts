import * as BABYLON from 'babylonjs';

import { GameManager } from '../Core/GameManager';
import { AbstractScene } from './AbstractScene';

export class DefaultScene extends AbstractScene {
  load() {
    GameManager.engine.displayLoadingUI();

    GameManager.setScene(
      this.prepareScene()
    );

    this.prepareNetworkClientAndJoinRoom('lobby').then(() => {
        this.prepareNetworkReplication();
    });

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

    // Create player
    var player = BABYLON.MeshBuilder.CreateCylinder('box', {});
    this.replicatePlayer(player);

    return scene;
  }
}
