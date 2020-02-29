import {
  Vector3,
  Color3,
  Mesh,
  Sound,
  Engine,
  AudioEngine,
  Analyser,
  VertexData,
  VertexBuffer,
  FreeCamera,
  StandardMaterial,
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
    let hemisphericLight = new HemisphericLight(
      'hemisphericLight',
      Vector3.Up(),
      this.babylonScene
    );
  }

  prepareVisualizer() {
    const name = 'visualizer';
    const segments = 32;
    const diameter = 5;
    const audioMultiplier = 0.01;
    let mesh = this.generatePolyMesh(
      name,
      segments,
      diameter
    );

    var track = new Sound(
      'track',
      '/static/audio/track01.mp3',
      this.babylonScene,
      () => {
        track.play();
      },
      {
        loop: true,
        autoplay: true,
      }
    );

    let analyser = new Analyser(this.babylonScene);
    (<AudioEngine>Engine.audioEngine).connectToAnalyser(analyser); // TODO: fix when PR is merged
    analyser.FFT_SIZE = segments * 2;
    //analyser.SMOOTHING = 0.9;

    this.babylonScene.registerBeforeRender(() => {
	    const workingArray = analyser.getByteFrequencyData();

      let diameterAddArray = [];
      for (let i = 0; i < analyser.getFrequencyBinCount(); i++) {
        diameterAddArray[i] = workingArray[i] * audioMultiplier;
      }

      const vertexData = this.generatePolyMeshVertexData(segments, diameter, diameterAddArray);
      vertexData.applyToMesh(mesh, true);
  	});
  }

  generatePolyMesh(name: string = 'shape', segments: number = 32, diameter: number = 5): Mesh {
    let mesh = new Mesh(
      name,
      this.babylonScene
    );
    let meshMaterial = new StandardMaterial(
      name + 'Material',
      this.babylonScene
    );
    meshMaterial.backFaceCulling = false;
    meshMaterial.emissiveColor = new Color3(1, 1, 1);
    mesh.material = meshMaterial;

    const vertexData = this.generatePolyMeshVertexData(segments, diameter);
    vertexData.applyToMesh(mesh, true);

    mesh.rotate(new Vector3(1, 0, 0), Math.PI);

    return mesh;
  }

  generatePolyMeshVertexData(segments: number, diameter: number, diameterAddArray?: Array<number>) {
    const positionsCount = segments + 2;
    const segmentWidth = Math.PI /* * 2 */ / segments;

    let vertexData = new VertexData();
    let positions = [];
    let indices = [];
    let colors = [];
    let normals = [];
    let angle = 0;

    // Calculate
    positions.push(0, 0, 0);
    colors.push(1, 0, 0, 1);

    for (let i = 1; i < positionsCount; i++) {
      let add = diameterAddArray && typeof diameterAddArray[i-2] !== 'undefined'
        ? diameterAddArray[i-2]
        : 0;

      const finalDiameter = diameter + add;

      const x = Math.cos(angle) * finalDiameter;
      const y = Math.sin(angle) * finalDiameter;

      positions.push(x, y, 0);
      colors.push(1, 0, 0, 1);

      angle -= segmentWidth;
      if (i > 1) {
        indices.push(0, i - 1, i);
      }
    }

    VertexData.ComputeNormals(positions, indices, normals);

    vertexData.positions = positions;
    vertexData.indices = indices;
    vertexData.colors = colors;
    vertexData.normals = normals;

    return vertexData;
  }
}
