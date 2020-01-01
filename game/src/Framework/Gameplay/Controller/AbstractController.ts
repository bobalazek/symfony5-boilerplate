import { GameManager } from '../../Core/GameManager';

/**
 * This class will control an Possessable object/entity.
 */
export class AbstractController {

    public inputEnabledOnlyOnPointerLock: boolean = true; // should we update & proess the input only only when pointer is locked?

    public start () {

        GameManager.engine.runRenderLoop(() => {
            if (
                this.inputEnabledOnlyOnPointerLock &&
                GameManager.engine.isPointerLock
            ) {
                this.update();
            }
        });

    }

    public update () {

        // this will run on every loop, before the possessable entity will get updated

    }

}
