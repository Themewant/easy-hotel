import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import Edit from './edit';
import save from './save';
import metadata from './block.json';

registerBlockType(metadata.name, {
	edit: Edit,
	save,
});

// Import shared slider
import './slider-init.js';
import domReady from '@wordpress/dom-ready';

domReady(() => {
	const observer = new MutationObserver(() => {
		jQuery(document).trigger('eshb-init-accomodation-gallery');
	});

	observer.observe(document.body, {
		childList: true,
		subtree: true,
	});

	jQuery(document).on('eshb-init-accomodation-gallery', () => {
		if (typeof window.initESHBAccomodationGallery === 'function') {
			window.initESHBAccomodationGallery(document);
		}
	});
});
