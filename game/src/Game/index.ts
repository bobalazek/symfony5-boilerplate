import { GameManager } from '../Framework/Core/GameManager';

// Third person game
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

// Audio visializer
/*
import { AbstractController } from '../Framework/Gameplay/Controller';
import { AudioVisualizerScene } from './Scenes/AudioVisualizerScene';

GameManager.boot({
  engineOptions: {
    stencil: true,
  },
  controller: AbstractController,
  defaultScene: AudioVisualizerScene,
});
*/

// Hot air balloon
import { AbstractController } from '../Framework/Gameplay/Controller';
import { HotAirBalloonScene } from './Scenes/HotAirBalloonScene';

GameManager.boot({
  engineOptions: {
    stencil: true,
  },
  controller: AbstractController,
  defaultScene: HotAirBalloonScene,
});
