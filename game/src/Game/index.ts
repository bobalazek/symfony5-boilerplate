import { GameManager } from '../Framework/Core/GameManager';
import { AbstractController } from '../Framework/Gameplay/Controller';
import { AudioVisualizerScene } from './Scenes/AudioVisualizerScene';

GameManager.boot({
  engineOptions: {
    stencil: true,
  },
  controller: AbstractController,
  defaultScene: AudioVisualizerScene,
});
