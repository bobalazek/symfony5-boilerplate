import {
    CharacterParametersInterface,
    CharacterDataInterface,
} from './CharacterHelpers';

export class Character {

    /**
     * Holds all the parameters data of that character.
     */
    private _parameters: CharacterParametersInterface;

    /**
     * Holds all the (real time) data of that character.
     */
    private _data: CharacterDataInterface;

}
