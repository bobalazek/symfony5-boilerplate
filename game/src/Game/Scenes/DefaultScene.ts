import * as BABYLON from 'babylonjs';

import { GameManager } from '../../Framework/Core/GameManager';
import { AbstractScene } from '../../Framework/Scenes/AbstractScene';

export class DefaultScene extends AbstractScene {
  load() {
    // Show preloader
    GameManager.engine.displayLoadingUI();

    // Prepare scene
    this.scene = new BABYLON.Scene(GameManager.engine);
    this.prepareCamera();
    this.prepareLights();
    this.prepareEnvironment();
    this.preparePlayer();
    this.prepareNetworkClientAndJoinRoom('lobby')
      .then(() => {
          this.prepareNetworkReplication();
      });

    // Inspector
    this.scene.debugLayer.show();

    // Set scene & hide preloader
    GameManager.setScene(this.scene);
    GameManager.engine.hideLoadingUI();
  }

  prepareCamera() {
    this.scene.createDefaultCamera(true, true, true);

    let camera = <BABYLON.ArcRotateCamera>this.scene.activeCamera;

    camera.alpha = Math.PI / 3;
    camera.beta = Math.PI / 3;
    camera.radius = 10;
    camera.upperBetaLimit = Math.PI / 2;
  }

  prepareLights() {
    this.scene.createDefaultLight(true);
  }

  prepareEnvironment() {
    let ground = BABYLON.MeshBuilder.CreateGround('ground', {
      width: 128,
      height: 128,
    });

    let groundMaterial = new BABYLON.StandardMaterial('groundMaterial', this.scene);

    let groundTexture = new BABYLON.Texture('/static/images/game/ground.jpg', this.scene);

    groundTexture.uScale = groundTexture.vScale = 16;

    groundMaterial.diffuseTexture = groundTexture;

    ground.material = groundMaterial;
  }

  preparePlayer() {
    let player = BABYLON.MeshBuilder.CreateCylinder('player', {
      height: 2,
    });
    player.position.y = 1;

    this.replicatePlayer(player);

    GameManager.playerController.posessTransformNode(player);
  }
}
