import {
  Vector3,
  MeshBuilder,
} from 'babylonjs';

import { GameManager } from '../../Framework/Core/GameManager';
import { AbstractNetworkScene } from '../../Framework/Scenes/NetworkScene';
import { RoomState } from '../../Framework/Network/Schemas/RoomState';
import { Transform } from '../../Framework/Network/Schemas/Transform';
import {
  GAME_SERVER_HOST,
  GAME_SERVER_PORT,
} from '../Config';

export class DefaultNetworkScene extends AbstractNetworkScene {
  public networkHost: string = GAME_SERVER_HOST;
  public networkPort: number = GAME_SERVER_PORT;

  load() {
    return new Promise((resolve) => {
      // Show preloader
      GameManager.engine.displayLoadingUI();

      this.prepareCamera();
      this.prepareLights();
      this.prepareEnvironment();
      this.prepareNetworkClientAndJoinRoom('lobby')
        .then(() => {
          this.prepareNetworkSync();
        });

      // Inspector
      this.babylonScene.debugLayer.show();

      // Hide preloader
      GameManager.engine.hideLoadingUI();

      resolve(this);
    });
  }

  prepareNetworkSync() {
    super.prepareNetworkSync();

    const networkRoomState = <RoomState>this.networkRoom.state;
    networkRoomState.transforms.onAdd = (transform: Transform, key: string) => {
      if (transform.type === 'player') {
        let transformMesh = MeshBuilder.CreateCylinder(transform.id, {
          height: 2,
        });

        transformMesh.position = new Vector3(
          transform.position.x,
          transform.position.y,
          transform.position.z
        );
        transformMesh.rotation = new Vector3(
          transform.rotation.x,
          transform.rotation.y,
          transform.rotation.z
        );
      }

      if (transform.ownerPlayerId === this.networkRoomPlayerSessionId) {
        this.controller.posessTransformNode(transformMesh);
      }
    };
  }
}
