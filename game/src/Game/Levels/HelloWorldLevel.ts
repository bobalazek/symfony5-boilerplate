import { Key as KeyboardKey } from 'ts-keycode-enum';

import {
  GAME_SERVER_PORT,
  GAME_SERVER_HOST,
  GAME_SERVER_UPDATE_RATE,
} from '../Config';
import { GameManager } from '../../Framework/Core/GameManager';
import { Entity } from '../../Framework/Gameplay/Entity';
import { AbstractBaseLevel } from './AbstractBaseLevel';

export class HelloWorldLevel extends AbstractBaseLevel {
  protected _serverEnabled: boolean = true;
  protected _serverRoomName: string = 'lobby';
  protected _serverHost: string = GAME_SERVER_HOST + ':' + GAME_SERVER_PORT;

  /**
   * What key do we need to press to open the chat input?
   */
  public showChatInputKeyCode: KeyboardKey = KeyboardKey.T;

  /**
   * What key do we need to press to close the chat input?
   */
  public hideChatInputKeyCode: KeyboardKey = KeyboardKey.Escape;

  /**
   * What is the still acceptable tolerance for position/rotation to send the update to the server?
   */
  public serverPlayerTransformUpdateTolerance: number = 0.001;

  public onPreStart(callback: () => void) {
    this._prepareUI();

    callback();
  }

  public start() {
    super.start();

    this._prepareNetworkSync();
  }

  public onReady() {
    window.dispatchEvent(new Event('preloader:hide'));
  }

  private _prepareUI() {
    // TODO
  }

  protected _prepareNetworkSync() {
    // TODO
  }

  private _getPlayerMesh(playerId: string): BABYLON.AbstractMesh {
    let player = BABYLON.MeshBuilder.CreateSphere(playerId, {
      diameterX: 1,
      diameterY: 2,
      diameterZ: 0.5,
    }, this.getScene());

    player.position = new BABYLON.Vector3(0, 2, 0);
    player.material = new BABYLON.StandardMaterial(playerId + '_playerMaterial', this.getScene());
    player.material.alpha = 0.8;
    player.physicsImpostor = new BABYLON.PhysicsImpostor(
      player,
      BABYLON.PhysicsImpostor.SphereImpostor,
      {
        mass: 1,
        restitution: 0,
      },
      this.getScene()
    );
    player.physicsImpostor.physicsBody.angularDamping = 1;

    return player;
  }
}
