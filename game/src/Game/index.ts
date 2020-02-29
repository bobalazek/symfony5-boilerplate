import { GameManager } from '../Framework/Core/GameManager';

/*
import { ThirdPersonController } from '../Framework/Gameplay/Controller';
import { ThirdPersonInputBindings } from '../Framework/Gameplay/InputBindings';
import { DefaultScene } from './Scenes/DefaultScene';

GameManager.boot({
  engineOptions: {
    stencil: true,
  },
  controller: ThirdPersonController,
  inputBindings: ThirdPersonInputBindings,
  defaultScene: DefaultScene,
});
*/

import { AbstractController } from '../Framework/Gameplay/Controller';
import { AudioVisualizerScene } from './Scenes/AudioVisualizerScene';

GameManager.boot({
  engineOptions: {
    stencil: true,
  },
  controller: AbstractController,
  defaultScene: AudioVisualizerScene,
});
