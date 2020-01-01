import 'babylonjs-materials';
import { Client, Room } from 'colyseus.js';

import { GameManager } from '../../Framework/Core/GameManager';
import { AbstractLevel } from '../../Framework/Level/AbstractLevel';

export class AbstractNetworkLevel extends AbstractLevel {

    // Network
    /** Is the networking enabled? */
    protected _serverEnabled: boolean = false;

    /** What is the server host, that we want to connect to? */
    protected _serverHost: string;

    /** The connection client for the server. */
    protected _serverClient: Client;

    /** The room on the server we are connected to. */
    protected _serverRoom: Room;

    /** The room name of the server we are connected to. */
    protected _serverRoomName: string;

    /** Must be a value between 0 and 1. Lover the value, more smooth the transition, but bigger the delay. */
    protected _serverInterpolationSmoothing: number = 0.2;

    /** It will ignore updates from server that are older than this value. */
    protected _serverInterpolationLastUpdateTolerance: number = 1000;

    public start() {

        this._prepareNetwork();

    }

    protected _prepareNetwork() {
        if (
            this._serverEnabled &&
            this._serverHost !== undefined &&
            this._serverRoomName !== undefined
        ) {
            this._serverClient = new Client('ws://' + this._serverHost);
            this._serverRoom = this._serverClient.join(this._serverRoomName);

            // Interpolation
            this.getScene().onBeforeRenderObservable.add(() => {
                const now = (new Date()).getTime();
                this.getScene().meshes.forEach((mesh: BABYLON.AbstractMesh) => {
                    if (
                        mesh.metadata !== null &&
                        mesh.metadata.serverReplicated === true &&
                        now - mesh.metadata.serverLastUpdate < this._serverInterpolationLastUpdateTolerance
                    ) {
                        mesh.metadata.clientLastUpdate = now;
                        mesh.position = BABYLON.Vector3.Lerp(
                            mesh.position,
                            mesh.metadata.serverPosition,
                            this._serverInterpolationSmoothing
                        );
                        mesh.rotationQuaternion = BABYLON.Quaternion.Slerp(
                            mesh.rotationQuaternion,
                            mesh.metadata.serverRotation,
                            this._serverInterpolationSmoothing
                        )
                    }
                });
            });
        }
    }

}
