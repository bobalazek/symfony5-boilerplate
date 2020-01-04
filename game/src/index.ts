import { GameManager } from './Game/Core/GameManager';

import { DefaultScene } from './Game/Scenes/DefaultScene';

// CSS
import '../public/css/app.css';

// Boot up the game!
GameManager.boot({
  defaultScene: new DefaultScene(),
});
