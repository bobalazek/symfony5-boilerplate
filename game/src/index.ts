import { GameManager } from './Framework/Core/GameManager';

import { DefaultScene } from './Game/Scenes/DefaultScene';
import { PlayerController } from './Game/Gameplay/PlayerController';
import { PlayerInputBindings } from './Game/Gameplay/PlayerInputBindings';

// CSS
import '../static/css/app.css';

// Boot up the game!
GameManager.boot({
  defaultScene: DefaultScene,
  playerController: PlayerController,
  playerInputBindings: PlayerInputBindings,
});
