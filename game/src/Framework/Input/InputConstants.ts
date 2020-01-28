import { Key as KeyboardKey } from 'ts-keycode-enum';

/********** Bindings **********/
export class AbstractInputBindings {
  axes: { [key: string]: Array<InputMappingInterface> } = {};
  actions: { [key: string]: Array<InputMappingInterface> } = {};
}

export interface InputBindingsInterface {
  axes: { [key: string]: Array<InputMappingInterface> };
  actions: { [key: string]: Array<InputMappingInterface> };
}

/********** Interfaces **********/
export interface InputDeviceInterface {
  bindEvents(): void;
  unbindEvents(): void;
  update(): void;
}

export interface InputMappingInterface {
  device: InputDeviceEnum;
  data: any;
}

export interface InputMappingDataInterface {
  scale: number;
}

export interface InputMappingAxisKeyboardDataInterface extends InputMappingDataInterface {
  keyCode: KeyboardKey;
}

export interface InputMappingAxisKeyboardInterface extends InputMappingInterface {
  device: InputDeviceEnum;
  data: InputMappingAxisKeyboardDataInterface;
}

export interface InputMappingAxisGamepadDataInterface extends InputMappingDataInterface {
  axis: InputGamepadAxisEnum;
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

export enum InputMouseButtonEnum {
  Left,
  Middle,
  Right,
}

export interface InputEnumStickValues {
  x: number;
  y: number;
}

export enum InputGamepadAxisEnum {
  LeftStickX,
  LeftStickY,
  RightStickX,
  RightStickY,
  LeftTrigger,
  RightTrigger,
  Triggers,
}

/**
 * Directly related to the InputGamepadAxisEnum enum and the InputGamepad class.
 */
export enum InputGamepadAxisPropertyEnum {
  LeftStickX = 'leftStickX',
  LeftStickY = 'leftStickY',
  RightStickX = 'rightStickX',
  RightStickY = 'rightStickY',
  LeftTrigger = 'leftTrigger',
  RightTrigger  = 'rightTrigger',
  Triggers  = 'triggers', // A special, virtual field. If left trigger is pressed, the value is negative. If right trigger is pressed, it's positive.
}

export enum InputGamepadButtonEnum {
  A,
  B,
  X,
  Y,
  Start,
  Back,
  LeftStick,
  RightStick,
  LB,
  RB,
  LT,
  RT,
  DPadUp,
  DPadDown,
  DPadLeft,
  DPadRight,
}

/**
 * Directly related to the InputGamepadButtonEnum enum and the InputGamepad class.
 */
export enum InputGamepadButtonPropertyEnum {
  A = 'buttonA',
  B = 'buttonB',
  X = 'buttonX',
  Y = 'buttonY',
  Start = 'buttonStart',
  Back = 'buttonBack',
  LeftStick = 'buttonLeftStick',
  RightStick = 'buttonRightStick',
  LB = 'buttonLB',
  RB = 'buttonRB',
  LT = 'leftTrigger',
  RT = 'rightTrigger',
  DPadUp = 'buttonDPadUp',
  DPadDown = 'buttonDPadDown',
  DPadLeft = 'buttonDPadLeft',
  DPadRight = 'buttonDPadRight',
}
