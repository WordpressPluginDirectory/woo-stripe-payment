import {Component} from '@wordpress/element';

class ErrorBoundary extends Component {

    constructor(props) {
        super(props);
        this.state = {hasError: false};
    }

    static getDerivedStateFromError(error) {
        return {hasError: true};
    }

    render() {
        if (this.state.hasError) {
            // silently fail
            return null;
        }
        return this.props.children;
    }
}

export default ErrorBoundary;