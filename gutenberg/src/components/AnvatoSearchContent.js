import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import {
  CheckboxControl,
  SelectControl,
  Button,
  TextControl,
  Spinner,
  withNotices
} from '@wordpress/components';
import { Component } from '@wordpress/element';
import { styles } from "./styles/AnvatoSearchContentStyles.js";

class AnvatoSearchContent extends Component {
  constructor(props) {
    super(...arguments);
    this.state = {
      items: [],
      searching: false,
      fetchingMoreData: false,
      hasMoreData: true,
      params: {
        query: '',
        page: 1,
        station: null,
        type: 'vod'
      },
      settings: {},
      warnings: [],
    };
  }

  componentDidMount() {
    const pathWithQuery = addQueryArgs('/anv-rest-service/get-settings');

    apiFetch({
      path: pathWithQuery,
      method: 'GET'
    }).then((settings) => {
      this.setState(prevState => ({
        ...prevState,
        params: {
          ...prevState.params,
          station: settings.owners && settings.owners.length > 0 ? settings.owners[0].value : null
        },
        settings: settings
      }));
    }).catch(this.apiErrorCallback);
  }

  apiErrorCallback = (response) => {
    let message = '';

    if(response.anv_rest_error){
      message = response.messages[0];
    } else {
      message = 'An error occured. Please try again.'
    }

    this.props.noticeOperations.createErrorNotice(message);
    this.setState((prevState) => {
      return {
        ...prevState,
        searching: false
      }
    });
  }

  doSearch = (loadMore = false) => {
    const pathWithQuery = addQueryArgs('/anv-rest-service/search', this.state.params);

    apiFetch({
      path: pathWithQuery,
      method: 'GET'
    }).then((items) => {
      this.setState((prevState) => {
        let mergedItems = loadMore
          ? prevState.items.concat(items)
          : items;

        return {
          ...prevState,
          items: mergedItems,
          fetchingMoreData: false,
          searching: false,
          hasMoreData: items.length > 0
        }
      });
    }).catch(this.apiErrorCallback);;
  }

  generateShortcode = (embedId, stationId, type, disablePreroll = false) => {
    const typeAttribute = 
      type == 'vod' || type == 'live' 
        ? 'video' 
        : type
    let shortcode = '[anvplayer ' + typeAttribute + '="' + embedId + '"';

    if (stationId) {
      shortcode = shortcode.concat(' station="' + stationId + '"');
    }

    if (disablePreroll) {
      shortcode = shortcode.concat(' no_pr="true"');
    }

    return shortcode.concat(']');
  }

  renderShortcodes = () => {
    const rendered = this.props.attributes.shortcodes.length > 0
      ? <div contentEditable="true">
        {this.props.attributes.shortcodes.map(code => <p>{code}</p>)}
      </div>
      : <div>
        <p>Click here to insert Anvato content</p>
      </div>;

    return rendered;
  }

  getShortcodes = () => {
    const { station, type } = this.state.params;
    return this.state.items
      .filter(i => i.selected == true)
      .map(k => this.generateShortcode(k.embed_id, station, type, k.disablePreroll == true))
  }

  handleSearch = () => {
    this.setState((prevState) => {
      return {
        ...prevState,
        params: {
          ...prevState.params,
          page: 1,
        },
        searching: true,
      }
    }, this.doSearch);
  }

  handleLoadMore = () => {
    this.setState((prevState) => {
      return {
        ...prevState,
        params: {
          ...prevState.params,
          page: prevState.params.page + 1,
        },
        fetchingMoreData: true
      }
    }, this.doSearch.bind(this, true));
  }

  handleSavePost = () => {
    this.props.setAttributes({ shortcodes: this.getShortcodes() });
    this.props.noticeOperations.removeAllNotices();
    this.props.noticeOperations.createNotice({
      status: 'success',
      content: 'Selected content(s) are inserted into the post.'
    });
  }

  handleItemPropChanges = (itemId, propName, propValue) => {
    if (itemId == null || itemId.trim() == '') {
      return;
    }

    let copiedItems = this.state.items.slice();
    const itemIndex = copiedItems.findIndex(listItem => listItem.id == itemId);
    copiedItems[itemIndex][propName] = propValue;

    this.setState((prevState) => {
      return {
        ...prevState,
        items: copiedItems
      }
    });
  }

  handleSearchParamChange = (key, value) => {
    this.setState((prevState) => {
      return {
        ...prevState,
        params: {
          ...prevState.params,
          [key]: value
        }
      }
    });
  }

  listItems = (items) => {
    let rendered;

    if (items.length > 0) {
      const content = items.map(item => {
        return this.getOneListItem(item, this.state.params.type);
      });
      rendered = <div style={styles.content.list}>
        {content}
      </div>;
    } else {
      rendered = <div style={styles.noContent}>
        <p>No content found</p>
      </div>;
    }

    return rendered;
  }

  getOneListItem = (item, type) => {
    const { details, thumbnail } = styles.content.item;
    const contentDuration = item.meta.duration
      ? <span style={thumbnail.duration}>{item.meta.duration}</span>
      : '';
    const disablePreroll = type != 'playlist' && type != 'feed'
      ? <CheckboxControl
        label="Disable Preroll"
        checked={item.disablePreroll == true}
        onClick={event => event.stopPropagation()}
        onChange={checked => this.handleItemPropChanges(item.id, 'disablePreroll', checked)}
      />
      : '';

    let containerStyle = Object.assign({}, styles.content.item.container);

    if (item.selected) {
      containerStyle.boxShadow = '0 0 0 1px #fff, 0 0 0 4px #cf242a';
    }

    const element = <div key={item.id} className="content-item-container" style={containerStyle} onClick={e => this.handleItemPropChanges(item.id, 'selected', item.selected != true)}>
      <div style={{ ...thumbnail.image, backgroundImage: `url(${item.thumbnail})` }}>
        {contentDuration}
      </div>
      <div style={details.container}>
        <div style={details.main.container}>
          <div style={details.main.title}>
            {item.title}
          </div>
          <div style={details.main.description}>
            {item.description}
          </div>
        </div>
        <div>
          {this.getMetaItems(item.meta)}
        </div>
        <div style={{ marginTop: '20px' }}>
          {disablePreroll}
        </div>
      </div>
    </div>;

    return element;
  }

  getMetaItems = (metaList) => {
    let metaListRendered = [];
    const { meta } = styles.content.item.details;
    const excludedItems = [
      'duration'
    ];

    for (const key in metaList) {
      if (metaList.hasOwnProperty(key)) {
        const value = metaList[key];

        if (value == null) {
          continue;
        }

        if (excludedItems.indexOf(key) > -1) {
          continue;
        }

        const template =
          <div key={key} style={meta.item}>
            <span className="content-item-details-meta-label" style={meta.label}>{key.split('_').join(' ')}</span>
            <span style={meta.value}>{value}</span>
          </div>;

        metaListRendered.push(template);
      }
    }

    return metaListRendered;
  }

  render() {
    const loadMore = this.state.hasMoreData 
      ? <Button
        onClick={this.handleLoadMore}
        isLarge={'true'}
        isBusy={this.state.fetchingMoreData}
        disabled={this.state.searching || this.state.fetchingMoreData || this.state.items.length == 0} >
        Load More
        </Button>
      : '';

    let content = '';

    if (this.props.isSelected) {
      if (this.state.searching) {
        content = <span style={styles.spinner}>
          <Spinner />
        </span>;
      } else {
        content = this.listItems(this.state.items);
      }
    } else {
      return this.renderShortcodes();
    }

    return <div>
      {this.props.noticeUI}
      <div id="anvroot" style={styles.anvroot}>
        <div style={styles.searchbar.container}>
          <div style={styles.searchbar.item}>
            <TextControl
              style={styles.searchbar.query}
              placeholder="Search for content"
              onChange={value => this.handleSearchParamChange('query', value)}
              value={this.state.params.query}
            >
            </TextControl>
          </div>
          <div style={styles.searchbar.item}>
            <SelectControl
              options={[{ value: null, label: 'Select a station', disabled: true }].concat(this.state.settings.owners || [])}
              value={this.state.params.station}
              onChange={value => this.handleSearchParamChange('station', value)}
              style={styles.toolbarItem}>
            </SelectControl>
          </div>
          <div style={styles.searchbar.item}>
            <SelectControl
              options={this.state.settings.content_types}
              value={this.state.params.type}
              onChange={value => this.handleSearchParamChange('type', value)}
              style={styles.searchbar.item} >
            </SelectControl>
          </div>
          <div style={{ marginLeft: '2px'}}>
            <Button
              onClick={this.handleSearch}
              isBusy={this.state.searching}
              disabled={this.state.searching || this.state.fetchingMoreData}
              isLarge={'true'}
              isPrimary={'true'}>
              Search
              </Button>
          </div>
        </div>
        <hr />
        <div style={styles.content.container}>
          <div style={styles.content.inner}>
            {content}
          </div>
        </div>
        <hr />
        <div style={styles.footer.container}>
          {loadMore}
          <Button
            onClick={this.handleSavePost}
            isPrimary={'true'}
            isLarge={'true'}
            disabled={this.state.searching || this.state.fetchingMoreData || this.state.items.length == 0}
            style={styles.footer.insertIntoPost}>
            Insert into post
            </Button>
        </div>
      </div>
    </div >;
  }
}

export default withNotices(AnvatoSearchContent);
