import 'babylonjs-materials';
import * as Ammo from 'ammo.js';

import { AbstractNetworkLevel } from '../../Framework/Level/AbstractNetworkLevel';

export class AbstractBaseLevel extends AbstractNetworkLevel {
  // General
  protected _skybox: BABYLON.Mesh;

  // Other
  protected _worldSize: number = 4096;
  public start() {
    super.start();

    this.getScene().collisionsEnabled = true;
    this.getScene().enablePhysics(
      new BABYLON.Vector3(0, -9.82, 0),
      new BABYLON.AmmoJSPlugin(true, Ammo)
    );

    this._prepareSkybox(this._worldSize);
    this._prepareGround(this._worldSize / 16);
    this._prepareLights();
  }

  protected _prepareSkybox(size: number) {
    this._skybox = BABYLON.Mesh.CreateBox('skybox', size, this.getScene());
    this._skybox.infiniteDistance = true;

    let skyboxMaterial = new BABYLON.SkyMaterial('skyboxMaterial', this.getScene());
    skyboxMaterial.backFaceCulling = false;
    skyboxMaterial.inclination = 0;
    skyboxMaterial.luminance = 1;
    skyboxMaterial.turbidity = 20;

    this._skybox.material = skyboxMaterial;
  }

  protected _prepareGround(size: number) {
      let ground = BABYLON.Mesh.CreateGround(
        'ground',
        size,
        size,
        2,
        this.getScene()
      );
      ground.position.y = 1;

      let groundMaterial = new BABYLON.StandardMaterial('groundMaterial', this.getScene());
      let groundTexture = new BABYLON.Texture('static/textures/ground_diffuse.jpg', this.getScene());

      groundTexture.uScale = groundTexture.vScale = size / 8;

      groundMaterial.diffuseTexture = groundTexture;

      ground.material = groundMaterial;

      ground.checkCollisions = true;

      ground.physicsImpostor = new BABYLON.PhysicsImpostor(
          ground,
          BABYLON.PhysicsImpostor.BoxImpostor,
          {
            mass: 0,
            restitution: 0,
          },
          this.getScene()
      );
  }

  protected _prepareLights() {
      let hemiLight = new BABYLON.HemisphericLight(
          'hemiLight',
          new BABYLON.Vector3(0, 0, 0),
          this.getScene()
      );
      hemiLight.intensity = 0.7;
  }
}
