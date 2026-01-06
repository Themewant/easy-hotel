/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import Edit from './edit';
import save from './save';
import metadata from './block.json';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType(metadata.name, {
	/**
	 * @see ./edit.js
	 */
	edit: Edit,

	/**
	 * @see ./save.js
	 */
	save,
});

// Import shared slider
import './slider-init.js';
import domReady from '@wordpress/dom-ready';

domReady(() => {
	const observer = new MutationObserver(() => {
		jQuery(document).trigger('eshb-init-sliders');
	});

	observer.observe(document.body, {
		childList: true,
		subtree: true,
	});

	jQuery(document).on('eshb-init-sliders', () => {
		if (typeof window.initESHBSlider === 'function') {
			window.initESHBSlider(document);
		}
	});
});
