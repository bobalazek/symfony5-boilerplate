import { Key as KeyboardKey } from 'ts-keycode-enum';

import { InputMouseButtonEnum } from './InputMouse';
import {
  InputGamepadAxisEnum,
  InputGamepadButtonEnum,
} from './InputGamepad';

/********** Bindings **********/

export class AbstractInputBindings {
  axes: { [key: string]: Array<InputMappingInterface> } = {};
  actions: { [key: string]: Array<InputMappingInterface> } = {};
}

export interface InputBindingsInterface {
  axes: { [key: string]: Array<InputMappingInterface> };
  actions: { [key: string]: Array<InputMappingInterface> };
}

/********** Mapping **********/

export interface InputMappingInterface {
  device: InputDeviceEnum;
  data: any;
}

export interface InputMappingDataInterface {}

export interface InputMappingAxisKeyboardDataInterface extends InputMappingDataInterface {
  keyCode: KeyboardKey;
  scale: number;
}

export interface InputMappingAxisKeyboardInterface extends InputMappingInterface {
  device: InputDeviceEnum;
  data: InputMappingAxisKeyboardDataInterface;
}

export interface InputMappingAxisGamepadDataInterface extends InputMappingDataInterface {
  axis: InputGamepadAxisEnum;
  scale: number;
}

export interface InputMappingAxisGamepadInterface extends InputMappingInterface {
  device: InputDeviceEnum;
  data: InputMappingAxisGamepadDataInterface;
}

export interface InputMappingAxisMouseDataInterface extends InputMappingDataInterface {
  axis: InputAxisEnum;
  scale: number;
}

export interface InputMappingAxisMouseInterface extends InputMappingInterface {
  device: InputDeviceEnum;
  data: InputMappingAxisMouseDataInterface;
}

export interface InputMappingActionKeyboardDataInterface extends InputMappingDataInterface {
  keyCode: KeyboardKey;
}

export interface InputMappingActionKeyboardInterface extends InputMappingInterface {
  device: InputDeviceEnum;
  data: InputMappingActionKeyboardDataInterface;
}

export interface InputMappingActionMouseDataInterface extends InputMappingDataInterface {
  button: InputMouseButtonEnum;
}

export interface InputMappingActionMouseInterface extends InputMappingInterface {
  device: InputDeviceEnum;
  data: InputMappingActionMouseDataInterface;
}

export interface InputMappingActionGamepadDataInterface extends InputMappingDataInterface {
  button: InputGamepadButtonEnum;
}

export interface InputMappingActionGamepadInterface extends InputMappingInterface {
  device: InputDeviceEnum;
  data: InputMappingActionGamepadDataInterface;
}

/********** Enums **********/

export enum InputAxisEnum {
  X,
  Y,
}

export enum InputModeEnum {
  KeyboardAndMouse,
  Gamepad,
  VR,
}

export enum InputDeviceEnum {
  Keyboard,
  Mouse,
  Gamepad,
  Touch,
  DeviceOrientation,
}
