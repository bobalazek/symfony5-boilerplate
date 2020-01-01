import * as React from 'react';
import * as ReactDOM from 'react-dom';

export class PreloaderComponent extends React.Component<PreloaderComponentProps, PreloaderComponentState> {

    constructor(props) {
        super(props);

        this.state = {
            show: props.show || true,
            percentage: props.percentage || 0,
            text: props.text || '',
        };

        this.onUpdate = this.onUpdate.bind(this);
        this.onShow = this.onShow.bind(this);
        this.onHide = this.onHide.bind(this);
    }

    componentDidMount() {
        window.addEventListener('preloader:update', this.onUpdate);
        window.addEventListener('preloader:show', this.onShow);
        window.addEventListener('preloader:hide', this.onHide);
    }

    componentWillUnmount() {
        window.removeEventListener('preloader:update', this.onUpdate);
        window.removeEventListener('preloader:show', this.onShow);
        window.removeEventListener('preloader:hide', this.onHide);
    }

    onUpdate(event) {
        this.setState((prevState) => {
            return {
                percentage: event.detail.percentage,
                text: event.detail.text,
            };
        });
    }

    onShow(event) {
        this.setState((prevState) => {
            return {
                show: true,
            };
        });
    }

    onHide(event) {
        this.setState((prevState) => {
            return {
                show: false,
            };
        });
    }

    render() {
        return React.createElement(
            'div',
            {
                id: 'preloader-wrapper',
                className: !this.state.show ? 'hidden' : '',
            },
            React.createElement(
                'div',
                {
                    id: 'preloader-text',
                },
                this.state.text
            ),
            React.createElement(
                'div',
                {
                    id: 'preloader-spinner',
                },
                React.createElement(
                    'i',
                    {
                        className: 'fa fa-spinner fa-spin fa-5x',
                    }
                )
            )
        );
    }

}

/********** Interfaces **********/

interface PreloaderComponentProps {}
interface PreloaderComponentState {
    show: boolean;
    percentage: number;
    text: string;
}
