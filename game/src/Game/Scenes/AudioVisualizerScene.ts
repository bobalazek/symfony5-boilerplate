import {
  Vector3,
  Mesh,
  VertexData,
  StandardMaterial,
  FreeCamera,
  HemisphericLight,
} from 'babylonjs';

import { GameManager } from '../../Framework/Core/GameManager';
import { AbstractScene } from '../../Framework/Scenes/Scene';

export class AudioVisualizerScene extends AbstractScene {
  load() {
    return new Promise((resolve) => {
      // Show preloader
      GameManager.engine.displayLoadingUI();

      this.prepareCamera();
      this.prepareLights();
      this.prepareVisualizer();

      // Inspector
      this.babylonScene.debugLayer.show();

      // Hide preloader
      GameManager.engine.hideLoadingUI();

      resolve(this);
    });
  }

  prepareCamera() {
    var camera = new FreeCamera(
      'camera',
      new Vector3(0, 0, -30),
      this.babylonScene
    );
    camera.attachControl(GameManager.canvas, true);

    this.setActiveCamera(camera);
  }

  prepareLights() {
    new HemisphericLight(
      'light',
      Vector3.Up(),
      this.babylonScene
    );
  }

  prepareVisualizer() {
    let visualizer = new Mesh('visualizer', this.babylonScene);
    let visualizerMaterial = new StandardMaterial('visualizerMaterial', this.babylonScene);
    visualizerMaterial.wireframe = true;
    visualizer.material = visualizerMaterial;

    // TODO
    let positions = [
      -5, 2, -3,
      -7, -2, -3,
      -3, -2, -3,
      5, 2, 3,
      7, -2, 3,
      3, -2, 3,
    ];
    let indices = [
      0, 1, 2, 3, 4, 5,
    ];

    let vertexData = new VertexData();

    vertexData.positions = positions;
    vertexData.indices = indices;

    vertexData.applyToMesh(visualizer);
  }
}
