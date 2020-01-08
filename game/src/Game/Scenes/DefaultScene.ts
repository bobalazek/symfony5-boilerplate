import * as BABYLON from 'babylonjs';

import { GameManager } from '../../Framework/Core/GameManager';
import { AbstractNetworkScene } from '../../Framework/Scenes/AbstractNetworkScene';
import { NetworkConstants } from '../../Framework/Network/NetworkConstants';

export class DefaultScene extends AbstractNetworkScene {
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
    let camera = new BABYLON.ArcRotateCamera(
      'camera',
      Math.PI / 3,
      Math.PI / 3,
      10,
      BABYLON.Vector3.Zero(),
      this.scene
    );

    camera.upperBetaLimit = Math.PI / 2;

    this.scene.activeCamera = camera

    camera.attachControl(GameManager.canvas);
  }

  prepareLights() {
    let light = new BABYLON.HemisphericLight(
      'light',
      new BABYLON.Vector3(0, 1, 0),
      this.scene
    );
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

    this.networkReplicate(
      GameManager.scene.getMeshByID(playerCharacterId)
    );
  }
}
