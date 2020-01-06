import { GameManager } from './Framework/Core/GameManager';

import { DefaultScene } from './Game/Scenes/DefaultScene';

// CSS
import '../static/css/app.css';

// Boot up the game!
GameManager.boot({
  defaultScene: new DefaultScene(),
});
