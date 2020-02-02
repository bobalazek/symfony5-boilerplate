import { GameManager } from '../Framework/Core/GameManager';
import { DefaultScene } from './Scenes/DefaultScene';
import { PlayerController } from './Gameplay/PlayerController';
import { PlayerInputBindings } from './Gameplay/PlayerInputBindings';

// Boot up the game!
GameManager.boot({
  engineOptions: {
    stencil: true,
  },
  defaultScene: DefaultScene,
  playerController: PlayerController,
  playerInputBindings: PlayerInputBindings,
});
