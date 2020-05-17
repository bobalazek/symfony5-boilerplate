import { MeshBuilder } from 'babylonjs';

import { GameManager } from '../../Framework/Core/GameManager';
import { AbstractNetworkScene } from '../../Framework/Scenes/NetworkScene';
import { NetworkConstants } from '../../Framework/Network/NetworkConstants';
import {
  GAME_SERVER_HOST,
  GAME_SERVER_PORT,
} from '../Config';

export class DefaultNetworkScene extends AbstractNetworkScene {
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
      this.babylonScene.debugLayer.show();

      // Hide preloader
      GameManager.engine.hideLoadingUI();

      resolve(this);
    });
  }

  preparePlayer(playerCharacterId: string = 'player') {
    let playerCharacter = MeshBuilder.CreateCylinder(playerCharacterId, {
      height: 2,
    });
    playerCharacter.position.y = 1;

    this.controller.posessTransformNode(playerCharacter);
  }

  preparePlayerNetworkSync(playerCharacterId: string) {
    this.networkRoom.send(
      NetworkConstants.PLAYER_TRANSFORM_NODE_ID_SET,
      playerCharacterId
    );

    this.networkReplicate(
      GameManager.babylonScene.getMeshByID(playerCharacterId)
    );
  }
}
