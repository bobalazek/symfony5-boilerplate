export interface PlayerInputBindingsInterface {
  axes: Array<PlayerInputBindingsMappingInterface>;
  actions: Array<PlayerInputBindingsMappingInterface>;
}

export interface PlayerInputBindingsMappingInterface {
  device: PlayerInputBindingsDeviceEnum;
  data: any;
}

export enum PlayerInputBindingsDeviceEnum {
  Keyboard,
  Mouse,
}

export abstract class AbstractPlayerInputBindings implements PlayerInputBindingsInterface {
  axes = [];
  actions = [];
}
