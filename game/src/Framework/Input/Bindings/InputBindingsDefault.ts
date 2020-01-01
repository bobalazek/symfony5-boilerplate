import { Key as KeyboardKey } from 'ts-keycode-enum';

import {
  InputAxisEnum,
  InputDeviceEnum,
  InputMappingInterface,
  AbstractInputBindings,
  InputMappingDataInterface,
  InputMappingAxisKeyboardInterface,
  InputMappingAxisMouseInterface,
  InputMappingAxisGamepadInterface,
  InputMappingActionKeyboardInterface,
  InputMappingActionMouseInterface,
  InputMappingActionGamepadInterface
} from '../InputHelpers';
import {
  InputGamepadAxisEnum,
  InputGamepadButtonEnum
} from '../../Input/InputGamepad';
import {
  InputMouseButtonEnum
} from '../../Input/InputMouse';

export class InputBindingsDefault extends AbstractInputBindings {
  axes: { [key: string]: Array<InputMappingInterface> } = {
    moveForward: [
      <InputMappingAxisKeyboardInterface>{
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode: KeyboardKey.UpArrow,
          scale: 1.0,
        },
      },
      <InputMappingAxisKeyboardInterface>{
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode: KeyboardKey.W,
          scale: 1.0,
        },
      },
      <InputMappingAxisKeyboardInterface>{
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode: KeyboardKey.DownArrow,
          scale: -1.0,
        },
      },
      <InputMappingAxisKeyboardInterface>{
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode: KeyboardKey.S,
          scale: -1.0,
        },
      },
      <InputMappingAxisGamepadInterface>{
        device: InputDeviceEnum.Gamepad,
        data: {
          axis: InputGamepadAxisEnum.LeftStickY,
          scale: 1.0,
        }
      },
    ],
    moveRight: [
      <InputMappingAxisKeyboardInterface>{
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode: KeyboardKey.LeftArrow,
          scale: -1.0,
        },
      },
      <InputMappingAxisKeyboardInterface>{
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode: KeyboardKey.A,
          scale: -1.0,
        },
      },
      <InputMappingAxisKeyboardInterface>{
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode: KeyboardKey.RightArrow,
          scale: 1.0,
        },
      },
      <InputMappingAxisKeyboardInterface>{
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode: KeyboardKey.D,
          scale: 1.0,
        },
      },
      <InputMappingAxisGamepadInterface>{
        device: InputDeviceEnum.Gamepad,
        data: {
          axis: InputGamepadAxisEnum.LeftStickX,
          scale: 1.0,
        }
      },
    ],
    lookUp: [
      <InputMappingAxisMouseInterface>{
        device: InputDeviceEnum.Mouse,
        data: {
          axis: InputAxisEnum.Y,
          scale: 1.0,
        }
      },
      <InputMappingAxisGamepadInterface>{
        device: InputDeviceEnum.Gamepad,
        data: {
          axis: InputGamepadAxisEnum.RightStickY,
          scale: 1.0,
        }
      },
    ],
    lookRight: [
      <InputMappingAxisMouseInterface>{
        device: InputDeviceEnum.Mouse,
        data: {
          axis: InputAxisEnum.X,
          scale: 1.0,
        }
      },
      <InputMappingAxisGamepadInterface>{
        device: InputDeviceEnum.Gamepad,
        data: {
          axis: InputGamepadAxisEnum.RightStickX,
          scale: 1.0,
        }
      },
    ],
  };

  actions: { [key: string]: Array<InputMappingInterface> } = {
    jump: [
      <InputMappingActionKeyboardInterface>{
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode: KeyboardKey.Space,
        },
      },
      <InputMappingActionGamepadInterface>{
        device: InputDeviceEnum.Gamepad,
        data: {
          button: InputGamepadButtonEnum.B,
        },
      },
    ],
    interact: [
      <InputMappingActionKeyboardInterface>{
        device: InputDeviceEnum.Keyboard,
        data: {
          keyCode: KeyboardKey.F,
        },
      },
      <InputMappingActionGamepadInterface>{
        device: InputDeviceEnum.Gamepad,
        data: {
          button: InputGamepadButtonEnum.A,
        },
      },
      <InputMappingActionMouseInterface>{
        device: InputDeviceEnum.Mouse,
        data: {
          button: InputMouseButtonEnum.Left,
        },
      },
    ],
  };

}
