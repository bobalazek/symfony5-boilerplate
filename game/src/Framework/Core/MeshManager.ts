import { AbstractLevel } from '../Level/AbstractLevel';

export class MeshManager {
    private _loading: { [key: string]: ((mesh: BABYLON.AbstractMesh) => void)[] };
    private _loaded: { [key: string]: BABYLON.AbstractMesh };

    constructor(private _level: AbstractLevel) {}

    public load(name: string, url: string, callback: (mesh: BABYLON.AbstractMesh) => void) {
      const path = `${ url }${ name }`;

      if (typeof this._loaded[path] !== 'undefined') {
        callback(this._loaded[path]);
      } else if (typeof this._loading[path] !== 'undefined') {
        this._loading[path].push(callback);
      } else {
        let meshTask = this._level.getAssetsManager().addMeshTask(
          'meshTask_' + path,
          '',
          url,
          name
        );
        meshTask.onSuccess = (task: BABYLON.MeshAssetTask) => {
          this._loaded[path] = <BABYLON.AbstractMesh>task.loadedMeshes[0];
          this._loaded[path].isVisible = false;

          callback(this._loaded[path]);

          // Also emmit the mesh to all the waiting callbacks
          for (let i = 0; i < this._loading[path].length; i++) {
            this._loading[path][i](this._loaded[path]);
          }

          // Cleanup
          delete this._loading[path];
        };
        this._loading[path] = [];
      }
    }
}
