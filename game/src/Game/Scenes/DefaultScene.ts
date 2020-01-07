import * as BABYLON from 'babylonjs';

import { GameManager } from '../../Framework/Core/GameManager';
import { AbstractScene } from '../../Framework/Scenes/AbstractScene';
import { NetworkConstants } from '../../Framework/Network/NetworkConstants';

export class DefaultScene extends AbstractScene {
  load() {
    // Show preloader
    GameManager.engine.displayLoadingUI();

    // Prepare scene
    this.scene = new BABYLON.Scene(GameManager.engine);
    this.prepareCamera();
    this.prepareLights();
    this.prepareEnvironment();
    this.prepareNetworkClientAndJoinRoom('lobby')
      .then(() => {
        const playerCharacterId = 'player_' + this.networkRoom.sessionId;
          this.prepareNetworkSync();
          this.preparePlayer(playerCharacterId);
          this.preparePlayerNetworkSync(playerCharacterId);
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

  preparePlayer(playerCharacterId: string = 'player') {
    let playerCharacter = BABYLON.MeshBuilder.CreateCylinder(playerCharacterId, {
      height: 2,
    });
    playerCharacter.position.y = 1;

    GameManager.playerController.posessTransformNode(playerCharacter);
  }

  preparePlayerNetworkSync(playerCharacterId: string) {
    this.networkRoom.send([
      NetworkConstants.PLAYER_TRANSFORM_NODE_SET,
      playerCharacterId
    ]);

    this.replicate(
      GameManager.scene.getMeshByID(playerCharacterId)
    );
  }
}
