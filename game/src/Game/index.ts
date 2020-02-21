import { GameManager } from '../Framework/Core/GameManager';
import { DefaultScene } from './Scenes/DefaultScene';
import { Controller } from './Gameplay/Controller';
import { InputBindings } from './Gameplay/InputBindings';

GameManager.boot({
  engineOptions: {
    stencil: true,
  },
  defaultScene: DefaultScene,
  controller: Controller,
  inputBindings: InputBindings,
});
