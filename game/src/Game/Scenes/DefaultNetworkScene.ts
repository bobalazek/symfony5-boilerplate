import * as BABYLON from 'babylonjs';

import { GameManager } from '../../Framework/Core/GameManager';
import { AbstractNetworkScene } from '../../Framework/Scenes/NetworkScene';
import { NetworkConstants } from '../../Framework/Network/NetworkConstants';
import {
  GAME_SERVER_HOST,
  GAME_SERVER_PORT,
} from '../Config';

export class DefaultScene extends AbstractNetworkScene {
  public networkHost: string = GAME_SERVER_HOST;
  public networkPort: number = GAME_SERVER_PORT;

  load() {
    return new Promise((resolve) => {
      // Show preloader
      GameManager.engine.displayLoadingUI();

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

      // Hide preloader
      GameManager.engine.hideLoadingUI();

      resolve(this);
    });
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