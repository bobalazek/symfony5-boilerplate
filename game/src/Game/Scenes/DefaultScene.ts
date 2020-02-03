import * as BABYLON from 'babylonjs';

import { GameManager } from '../../Framework/Core/GameManager';
import { AbstractScene } from '../../Framework/Scenes/Scene';

export class DefaultScene extends AbstractScene {
  load() {
    const playerCharacterId = 'player';

    this.afterLoadObservable.add(() => {
      GameManager.playerController.posessTransformNode(
        GameManager.scene.getMeshByID(playerCharacterId)
      );
    })

    return new Promise((resolve) => {
      // Show preloader
      GameManager.engine.displayLoadingUI();

      this.prepareCamera();
      this.prepareLights();
      this.prepareEnvironment();
      this.preparePlayer(playerCharacterId);

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
  }
}
