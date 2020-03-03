import { Component } from '@wordpress/element';

class AnvatoSaveContent extends Component {
  constructor(props) {
    super(...arguments);
  }

  render() {
    const { shortcodes } = this.props.attributes;

    return shortcodes.length > 0 && <div>
      {shortcodes.map(code => <p>{code}</p>)}
    </div>
  }
}

export default AnvatoSaveContent