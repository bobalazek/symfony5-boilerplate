import { Key as KeyboardKey } from 'ts-keycode-enum';

export interface PlayerInputBindingsMappingInterface {
  device: PlayerInputBindingsDeviceEnum;
  data: any;
}

export enum PlayerInputBindingsDeviceEnum {
  Keyboard,
  Mouse,
}

export enum PlayerInputAxisEnum {
  X,
  Y,
}

export enum PlayerInputMouseButtonEnum {
  Left,
  Middle,
  Right,
}

export interface PlayerInputBindingsInterface {
  axes: { [key: string]: Array<PlayerInputBindingsMappingInterface> };
  actions: { [key: string]: Array<PlayerInputBindingsMappingInterface> };
}

export abstract class AbstractPlayerInputBindings implements PlayerInputBindingsInterface {
  axes = {
    moveForward: [
      {
        device: PlayerInputBindingsDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.UpArrow,
          scale: 1.0,
        },
      },
      {
        device: PlayerInputBindingsDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.W,
          scale: 1.0,
        },
      },
      {
        device: PlayerInputBindingsDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.DownArrow,
          scale: -1.0,
        },
      },
      {
        device: PlayerInputBindingsDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.S,
          scale: -1.0,
        },
      },
    ],
    moveRight: [
      {
        device: PlayerInputBindingsDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.RightArrow,
          scale: 1.0,
        },
      },
      {
        device: PlayerInputBindingsDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.D,
          scale: 1.0,
        },
      },
      {
        device: PlayerInputBindingsDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.LeftArrow,
          scale: -1.0,
        },
      },
      {
        device: PlayerInputBindingsDeviceEnum.Keyboard,
        data: {
          keyCode:  KeyboardKey.A,
          scale: -1.0,
        },
      },
    ],
    lookUp: [
      {
        device: PlayerInputBindingsDeviceEnum.Mouse,
        data: {
          axis:  PlayerInputAxisEnum.Y,
          scale: 1.0,
        },
      },
    ],
    lookRight: [
      {
        device: PlayerInputBindingsDeviceEnum.Mouse,
        data: {
          axis:  PlayerInputAxisEnum.X,
          scale: 1.0,
        },
      },
    ],
  };
  actions = {
    interact: [
      {
          device: PlayerInputBindingsDeviceEnum.Keyboard,
          data: {
              keyCode: KeyboardKey.F,
          },
      },
      {
          device: PlayerInputBindingsDeviceEnum.Mouse,
          data: {
              button: PlayerInputMouseButtonEnum.Left,
          },
      },
    ],
  };
}
