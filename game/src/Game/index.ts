import { GameManager } from '../Framework/Core/GameManager';

import { ThirdPersonController } from '../Framework/Gameplay/Controller';
import { ThirdPersonInputBindings } from '../Framework/Gameplay/InputBindings';
import { DefaultNetworkScene } from './Scenes/DefaultNetworkScene';

GameManager.boot({
  engineOptions: {
    stencil: true,
  },
  controller: ThirdPersonController,
  inputBindings: ThirdPersonInputBindings,
  defaultScene: DefaultNetworkScene,
});
