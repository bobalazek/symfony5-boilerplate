import * as React from 'react';
import * as ReactDOM from 'react-dom';

export class DebugComponent extends React.Component<DebugComponentProps, DebugComponentState> {

    constructor(props) {
        super(props);

        this.state = {
            ping: props.ping || 0,
            fps: props.fps || 0,
        };

        this.onUpdate = this.onUpdate.bind(this);
    }

    componentDidMount() {
        window.addEventListener('debug:update', this.onUpdate);
    }

    componentWillUnmount() {
        window.removeEventListener('debug:update', this.onUpdate);
    }

    onUpdate(event) {
        this.setState((prevState) => {
            return {
                ping: event.detail.ping,
                fps: event.detail.fps,
            };
        });
    }

    render() {
        return React.createElement(
            'div',
            {
                id: 'debug-wrapper',
            },
            React.createElement(
                'div',
                {
                    id: 'ping-counter',
                },
                'Ping: ' + ((this.state.ping >= 0) ? this.state.ping : 'unknown'),
            ),
            React.createElement(
                'div',
                {
                    id: 'fps-counter',
                },
                'FPS: ' + this.state.fps
            )
        );
    }

}

/********** Interfaces **********/

interface DebugComponentProps {}
interface DebugComponentState {
    ping: number;
    fps: boolean;
}
