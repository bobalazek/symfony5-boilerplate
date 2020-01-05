import * as BABYLON from 'babylonjs';

import { GameManager } from '../Core/GameManager';
import { AbstractScene } from './AbstractScene';

export class DefaultScene extends AbstractScene {
  load() {
    GameManager.engine.displayLoadingUI();

    GameManager.setScene(
      this.prepare()
    );

    this.prepareNetworkClientAndJoinRoom('lobby').then(() => {
        this.prepareNetworkReplication();
    });

    GameManager.engine.hideLoadingUI();
  }

  prepare(): BABYLON.Scene {
    let scene = new BABYLON.Scene(GameManager.engine);

    scene.createDefaultCameraOrLight(true, true, true);

    let camera = <BABYLON.ArcRotateCamera>scene.activeCamera;
    camera.alpha = Math.PI / 3;
    camera.beta = Math.PI / 3;
    camera.radius = 10;

    // Ground
    let ground = BABYLON.MeshBuilder.CreateGround('ground', {
      width: 128,
      height: 128,
    });
    let groundMaterial = new BABYLON.StandardMaterial('groundMaterial', scene);
    let groundTexture = new BABYLON.Texture('/static/images/game/ground.jpg', scene);
    groundTexture.uScale = groundTexture.vScale = 16;
    groundMaterial.diffuseTexture = groundTexture;
    ground.material = groundMaterial;

    // Player
    let player = BABYLON.MeshBuilder.CreateCylinder('player', {
      height: 2,
    });
    player.position.y = 1;
    this.replicatePlayer(player);
    GameManager.playerController.posessTransformNode(player);

    // Inspector
    scene.debugLayer.show();

    return scene;
  }
}
