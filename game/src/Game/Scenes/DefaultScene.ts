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

  preparePlayer(playerCharacterId: string = 'player') {
    let playerCharacter = BABYLON.MeshBuilder.CreateCylinder(playerCharacterId, {
      height: 2,
    });
    playerCharacter.position.y = 1;

    GameManager.playerController.posessTransformNode(playerCharacter);
  }

  preparePlayerNetworkSync(playerCharacterId: string) {
    this.networkRoom.send([
      NetworkConstants.PLAYER_TRANSFORM_NODE_ID_SET,
      playerCharacterId
    ]);

    this.networkReplicate(
      GameManager.scene.getMeshByID(playerCharacterId)
    );
  }
}
