import { MeshBuilder } from 'babylonjs';

import { GameManager } from '../../Framework/Core/GameManager';
import { AbstractScene } from '../../Framework/Scenes/Scene';

export class DefaultScene extends AbstractScene {
  load() {


    return new Promise((resolve) => {
      // Show preloader
      GameManager.engine.displayLoadingUI();

      const playerCharacterId = 'player';

      this.prepareCamera();
      this.prepareLights();
      this.prepareEnvironment();
      this.preparePlayer(playerCharacterId);
      GameManager.playerController.posessTransformNode(
        this.babylonScene.getMeshByID(playerCharacterId)
      );

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
  }
}
