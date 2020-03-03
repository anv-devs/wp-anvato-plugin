import AnvatoIcon from "./components/AnvatoIcon.js";
import AnvatoSearchContent from "./components/AnvatoSearchContent.js";
import AnvatoSaveContent from "./components/AnvatoSaveContent.js";

wp.blocks.registerBlockType('anvato/anv-block', {
  title: 'Anvato',
  icon: AnvatoIcon,
  category: 'embed',
  attributes: {
    shortcodes: {
      type: 'array',
      default: [],
    }
  },
  edit: AnvatoSearchContent,
  save: AnvatoSaveContent
});