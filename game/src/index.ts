import { GameManager } from './Framework/Core/GameManager';
import { InputBindingsDefault } from './Framework/Input/Bindings/InputBindingsDefault';
import { PlayerController } from './Framework/Gameplay/Controller/PlayerController';

import { DEBUG } from './Game/Config';
import { HelloWorldLevel } from './Game/Levels/HelloWorldLevel';

// CSS
import '../public/css/app.css';

// Boot up the game!
GameManager.boot({
    debug: DEBUG,
    startupLevel: HelloWorldLevel,
    inputBindings: InputBindingsDefault,
    controller: PlayerController,
});
