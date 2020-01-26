import { Key as KeyboardKey } from 'ts-keycode-enum';

import {
  InputBindingsInterface,
  InputDeviceEnum,
  InputAxisEnum,
  InputMouseButtonEnum,
} from '../Input/InputConstants';

export abstract class AbstractPlayerInputBindings implements InputBindingsInterface {
  axes = {
    moveForward: [
      {
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.UpArrow,
          scale: 1.0,
        },
      },
      {
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.W,
          scale: 1.0,
        },
      },
      {
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.DownArrow,
          scale: -1.0,
        },
      },
      {
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.S,
          scale: -1.0,
        },
      },
    ],
    moveRight: [
      {
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.RightArrow,
          scale: 1.0,
        },
      },
      {
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.D,
          scale: 1.0,
        },
      },
      {
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.LeftArrow,
          scale: -1.0,
        },
      },
      {
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.A,
          scale: -1.0,
        },
      },
    ],
    lookUp: [
      {
        device: InputDeviceEnum.Mouse,
        data: {
          axis:  InputAxisEnum.Y,
          scale: 1.0,
        },
      },
    ],
    lookRight: [
      {
        device: InputDeviceEnum.Mouse,
        data: {
          axis:  InputAxisEnum.X,
          scale: 1.0,
        },
      },
    ],
  };
  actions = {
    interact: [
      {
          device: InputDeviceEnum.Keyboard,
          data: {
              keyCode: KeyboardKey.F,
          },
      },
      {
          device: InputDeviceEnum.Mouse,
          data: {
              button: InputMouseButtonEnum.Left,
          },
      },
    ],
  };
}
