import {
  Vector3,
  FreeCamera,
  HemisphericLight,
  SceneLoader,
  PhysicsImpostor,
  AmmoJSPlugin,
} from 'babylonjs';
import'babylonjs-loaders';
import * as Ammo from 'ammo.js';

import { GameManager } from '../../Framework/Core/GameManager';
import { AbstractScene } from '../../Framework/Scenes/Scene';

export class HotAirBalloonScene extends AbstractScene {
  load() {
    return new Promise((resolve) => {
      // Show preloader
      GameManager.engine.displayLoadingUI();

      this.preparePhysics();
      this.prepareCamera();
      this.prepareLights();
      this.prepareEnvironment();
      this.prepareHotAirBalloon();

      // Inspector
      this.babylonScene.debugLayer.show();

      // Hide preloader
      GameManager.engine.hideLoadingUI();

      resolve(this);
    });
  }

  preparePhysics() {
    const physicsPlugin = new AmmoJSPlugin(true, Ammo.default());
    this.babylonScene.enablePhysics(null, physicsPlugin)
  }

  prepareCamera() {
    var camera = new FreeCamera(
      'camera',
      new Vector3(0, 2, -20),
      this.babylonScene
    );
    camera.attachControl(GameManager.canvas, true);

    this.setActiveCamera(camera);
  }

  prepareLights() {
    new HemisphericLight(
      'hemisphericLight',
      Vector3.Up(),
      this.babylonScene
    );
  }

  prepareEnvironment() {
    super.prepareEnvironment();

    let ground = this.babylonScene.getMeshByName('ground');
    ground.physicsImpostor = new PhysicsImpostor(
      ground,
      PhysicsImpostor.PlaneImpostor,
      { mass: 0 },
      this.babylonScene
    );
  }

  prepareHotAirBalloon() {
    SceneLoader.ImportMesh(
      'HotAirBalloon',
      '/static/models/HotAirBalloon/',
      'HotAirBalloon.glb',
      this.babylonScene,
      () => {
        let hotAirBalloon = this.babylonScene.getTransformNodeByName('HotAirBalloon');
        hotAirBalloon.position = new Vector3(0, 5, 0);

        let hotAirBalloonBasketSupport = this.babylonScene.getMeshByName('Basket_Support');
        hotAirBalloonBasketSupport.physicsImpostor = new PhysicsImpostor(
          hotAirBalloonBasketSupport,
          PhysicsImpostor.MeshImpostor,
          { mass: 10 },
          this.babylonScene
        );

        let hotAirBalloonBasket = this.babylonScene.getMeshByName('Basket');
        hotAirBalloonBasket.physicsImpostor = new PhysicsImpostor(
          hotAirBalloonBasket,
          PhysicsImpostor.MeshImpostor,
          { mass: 10 },
          this.babylonScene
        );
      }
    );
  }
}
