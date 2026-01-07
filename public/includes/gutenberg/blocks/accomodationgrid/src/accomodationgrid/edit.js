/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import {
	PanelBody,
	__experimentalHeading as Heading,
	BoxControl,
	__experimentalDivider as Divider,
	TabPanel,
	__experimentalNumberControl as NumberControl,
	SelectControl,
	TextControl,
	ToggleControl
} from '@wordpress/components';
import BackgroundControl from '../../../custom-components/BackgroundControl';
import BoxShadowControls from '../../../custom-components/BoxShadowControls';
import TypographyControls from '../../../custom-components/TypographyControls';
import ColorPopover from '../../../custom-components/ColorPopover';
/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
	const imageSizeOptions = useSelect((select) => {
		const blockEditorStore = select('core/block-editor');
		const editorStore = select('core'); // Testing 'core' store as well

		const blockEditorSettings = blockEditorStore && typeof blockEditorStore.getSettings === 'function' ? blockEditorStore.getSettings() : null;
		const coreSettings = editorStore && typeof editorStore.getSettings === 'function' ? editorStore.getSettings() : null;

		const sizes = blockEditorSettings?.imageSizes || coreSettings?.imageSizes;

		let options = [];

		if (sizes && Array.isArray(sizes)) {
			options = sizes.map((size) => ({
				label: size.name,
				value: size.slug,
			}));
		} else {
			options = [
				{ label: __('Large', 'easy-hotel'), value: 'large' },
				{ label: __('Medium', 'easy-hotel'), value: 'medium' },
				{ label: __('Thumbnail', 'easy-hotel'), value: 'thumbnail' },
			];
		}

		// Ensure our custom size is always there if missing
		if (!options.find(o => o.value === 'eshb_thumbnail')) {
			options.unshift({ label: __('Easy Hotel Thumbnail', 'easy-hotel'), value: 'eshb_thumbnail' });
		}

		return options;
	}, []);

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				{/* {query settings panel group} */}
				<PanelBody title={__('Query', 'easy-hotel')} initialOpen={false}>
					<NumberControl
						label={__('Posts Per Page (Only for custom grid page not archive page)', 'easy-hotel')}
						value={attributes.per_page}
						onChange={(value) => setAttributes({ per_page: value })}
						help={__('Number of rooms to display. Only for custom grid page not archive page', 'easy-hotel')}
					/>
					<SelectControl
						label={__('Order', 'easy-hotel')}
						value={attributes.room_order}
						onChange={(value) => setAttributes({ room_order: value })}
						options={[
							{ label: __('Ascending', 'easy-hotel'), value: 'ASC' },
							{ label: __('Descending', 'easy-hotel'), value: 'DESC' },
						]}
						help={__('Order of rooms to display. Only for custom grid page not archive page', 'easy-hotel')}
					/>
					<SelectControl
						label={__('Order By', 'easy-hotel')}
						value={attributes.room_orderby}
						onChange={(value) => setAttributes({ room_orderby: value })}
						options={[
							{ label: __('Date', 'easy-hotel'), value: 'date' },
							{ label: __('Title', 'easy-hotel'), value: 'title' },
							{ label: __('Name', 'easy-hotel'), value: 'name' },
							{ label: __('ID', 'easy-hotel'), value: 'id' },
							{ label: __('Random', 'easy-hotel'), value: 'rand' },
						]}
						help={__('Order of rooms to display. Only for custom grid page not archive page', 'easy-hotel')}
					/>
					<NumberControl
						label={__('Offset', 'easy-hotel')}
						value={attributes.room_offset}
						onChange={(value) => setAttributes({ room_offset: value })}
						help={__('Number of rooms to skip. Only for custom grid page not archive page', 'easy-hotel')}
					/>
					<ToggleControl
						label={__('Show Pagination', 'easy-hotel')}
						checked={attributes.show_pagination}
						onChange={(value) => setAttributes({ show_pagination: value })}
					/>
				</PanelBody>
				{ /* content settings panel group */}
				<PanelBody title={__('Content', 'easy-hotel')} initialOpen={false}>
					<SelectControl
						label={__('Style', 'easy-hotel')}
						value={attributes.grid_style}
						onChange={(value) => setAttributes({ grid_style: value })}
						options={[
							{ label: __('Default', 'easy-hotel'), value: 'default' },
							{ label: __('Style 1', 'easy-hotel'), value: '1' },
							{ label: __('Style 2', 'easy-hotel'), value: '2' },
							{ label: __('Style 3', 'easy-hotel'), value: '3' },
						]}
					/>
					<SelectControl
						label={__('Columns', 'easy-hotel')}
						value={attributes.room_columns}
						onChange={(value) => setAttributes({ room_columns: value })}
						options={[
							{ label: __('1 Column', 'easy-hotel'), value: 1 },
							{ label: __('2 Column', 'easy-hotel'), value: 2 },
							{ label: __('3 Column', 'easy-hotel'), value: 3 },
							{ label: __('4 Column', 'easy-hotel'), value: 4 },
							{ label: __('6 Column', 'easy-hotel'), value: 6 },
						]}
					/>
					<SelectControl
						label={__('Thumbnail Size', 'easy-hotel')}
						value={attributes.thumbnail_size}
						onChange={(value) => setAttributes({ thumbnail_size: value })}
						options={imageSizeOptions}
					/>
					<TextControl
						label={__('Button Text', 'easy-hotel')}
						value={attributes.btn_text}
						onChange={(value) => setAttributes({ btn_text: value })}
					/>
				</PanelBody>

			</InspectorControls>
			<InspectorControls group='styles'>
				<PanelBody title={__('Item', 'easy-hotel')} initialOpen={false}>
					<TabPanel
						className="eshb-tab-panel"
						activeClass="is-active"
						tabs={[
							{ name: 'normal', title: __('Normal', 'easy-hotel'), className: 'eshb-tab-normal' },
							{ name: 'hover', title: __('Hover', 'easy-hotel'), className: 'eshb-tab-hover' },
						]}
					>
						{(tab) => {
							const isHover = tab.name === 'hover';
							return (
								<div style={{ marginTop: '15px' }}>
									<BackgroundControl
										label={isHover ? __('Background (Hover)', 'easy-hotel') : __('Background', 'easy-hotel')}
										colorValue={isHover ? attributes.itemBackgroundColorHover : attributes.itemBackgroundColor}
										gradientValue={isHover ? attributes.itemBackgroundGradientHover : attributes.itemBackgroundGradient}
										onColorChange={(value) => {
											const hex = (value && typeof value === 'object') ? value.hex : value;
											setAttributes({ [isHover ? 'itemBackgroundColorHover' : 'itemBackgroundColor']: hex });
										}}
										onGradientChange={(value) => setAttributes({ [isHover ? 'itemBackgroundGradientHover' : 'itemBackgroundGradient']: value })}
									/>
									<BackgroundControl
										label={isHover ? __('Overlay One (Hover)', 'easy-hotel') : __('Overlay One', 'easy-hotel')}
										colorValue={isHover ? attributes.itemOverlayBackgroundColorHover : attributes.itemOverlayBackgroundColor}
										gradientValue={isHover ? attributes.itemOverlayBackgroundGradientHover : attributes.itemOverlayBackgroundGradient}
										onColorChange={(value) => {
											const hex = (value && typeof value === 'object') ? value.hex : value;
											setAttributes({ [isHover ? 'itemOverlayBackgroundColorHover' : 'itemOverlayBackgroundColor']: hex });
										}}
										onGradientChange={(value) => setAttributes({ [isHover ? 'itemOverlayBackgroundGradientHover' : 'itemOverlayBackgroundGradient']: value })}
									/>
									<BackgroundControl
										label={isHover ? __('Overlay Two (Hover)', 'easy-hotel') : __('Overlay Two', 'easy-hotel')}
										colorValue={isHover ? attributes.itemOverlayBackgroundColorTwoHover : attributes.itemOverlayBackgroundColorTwo}
										gradientValue={isHover ? attributes.itemOverlayBackgroundGradientTwoHover : attributes.itemOverlayBackgroundGradientTwo}
										onColorChange={(value) => {
											const hex = (value && typeof value === 'object') ? value.hex : value;
											setAttributes({ [isHover ? 'itemOverlayBackgroundColorTwoHover' : 'itemOverlayBackgroundColorTwo']: hex });
										}}
										onGradientChange={(value) => setAttributes({ [isHover ? 'itemOverlayBackgroundGradientTwoHover' : 'itemOverlayBackgroundGradientTwo']: value })}
									/>
								</div>
							);
						}}
					</TabPanel>
					<Divider />
					<TextControl
						label={__('Item Gap', 'easy-hotel')}
						value={attributes.itemGap}
						onChange={(value) => setAttributes({ itemGap: value })}
					/>
					<Divider />
					<BoxControl
						label={__('Padding', 'easy-hotel')}
						values={attributes.itemPadding}
						onChange={(value) => setAttributes({ itemPadding: value })}
					/>
					<Divider />
					<BoxControl
						label={__('Border Radious', 'easy-hotel')}
						values={attributes.itemBorderRadius}
						onChange={(nextValues) => setAttributes({ itemBorderRadius: nextValues })}
					/>
				</PanelBody>

				<PanelBody title={__('Title', 'easy-hotel')} initialOpen={false}>
					<TabPanel
						className="eshb-tab-panel"
						activeClass="is-active"
						tabs={[
							{ name: 'normal', title: __('Normal', 'easy-hotel'), className: 'eshb-tab-normal' },
							{ name: 'hover', title: __('Hover', 'easy-hotel'), className: 'eshb-tab-hover' },
						]}
					>
						{(tab) => {
							const isHover = tab.name === 'hover';
							return (
								<div style={{ marginTop: '15px' }}>
									<ColorPopover
										label={isHover ? __('Color (Hover)', 'easy-hotel') : __('Color', 'easy-hotel')}
										color={isHover ?
											attributes.itemTitleColorHover
											: attributes.itemTitleColor}
										defaultColor={isHover ? '' : ''}
										onChange={(value) => {
											const hex = (value && typeof value === 'object') ? value.hex : value;
											setAttributes({ [isHover ? 'itemTitleColorHover' : 'itemTitleColor']: hex });
										}}
									/>
								</div>
							);
						}}
					</TabPanel>
					<Divider />
					<BoxControl
						label={__('Padding', 'easy-hotel')}
						values={attributes.itemTitlePadding}
						onChange={(value) => setAttributes({ itemTitlePadding: value })}
					/>
					<Divider />
					<BoxControl
						label={__('Margin', 'easy-hotel')}
						values={attributes.itemTitleMargin}
						onChange={(value) => setAttributes({ itemTitleMargin: value })}
					/>
					<Divider />
					<TypographyControls
						label={__('Typography', 'easy-hotel')}
						attributes={attributes}
						setAttributes={setAttributes}
						attributeKey="itemTitleTypography"
					/>
				</PanelBody>

				<PanelBody title={__('Description', 'easy-hotel')} initialOpen={false}>
					<TabPanel
						className="eshb-tab-panel"
						activeClass="is-active"
						tabs={[
							{ name: 'normal', title: __('Normal', 'easy-hotel'), className: 'eshb-tab-normal' },
							{ name: 'hover', title: __('Hover', 'easy-hotel'), className: 'eshb-tab-hover' },
						]}
					>
						{(tab) => {
							const isHover = tab.name === 'hover';
							return (
								<div style={{ marginTop: '15px' }}>
									<ColorPopover
										label={isHover ? __('Color (Hover)', 'easy-hotel') : __('Color', 'easy-hotel')}
										color={isHover ?
											attributes.itemDescriptionColorHover
											: attributes.itemDescriptionColor}
										defaultColor={isHover ? '' : ''}
										onChange={(value) => {
											const hex = (value && typeof value === 'object') ? value.hex : value;
											setAttributes({ [isHover ? 'itemDescriptionColorHover' : 'itemDescriptionColor']: hex });
										}}
									/>
								</div>
							);
						}}
					</TabPanel>
					<Divider />
					<BoxControl
						label={__('Padding', 'easy-hotel')}
						values={attributes.itemDescriptionPadding}
						onChange={(value) => setAttributes({ itemDescriptionPadding: value })}
					/>
					<Divider />
					<BoxControl
						label={__('Margin', 'easy-hotel')}
						values={attributes.itemDescriptionMargin}
						onChange={(value) => setAttributes({ itemDescriptionMargin: value })}
					/>
					<Divider />
					<TypographyControls
						label={__('Typography', 'easy-hotel')}
						attributes={attributes}
						setAttributes={setAttributes}
						attributeKey="itemDescriptionTypography"
					/>
				</PanelBody>

				<PanelBody title={__('Capacities', 'easy-hotel')} initialOpen={false}>
					<TabPanel
						className="eshb-tab-panel"
						activeClass="is-active"
						tabs={[
							{ name: 'normal', title: __('Normal', 'easy-hotel'), className: 'eshb-tab-normal' },
							{ name: 'hover', title: __('Hover', 'easy-hotel'), className: 'eshb-tab-hover' },
						]}
					>
						{(tab) => {
							const isHover = tab.name === 'hover';
							return (
								<div style={{ marginTop: '15px' }}>
									<ColorPopover
										label={isHover ? __('Color (Hover)', 'easy-hotel') : __('Color', 'easy-hotel')}
										color={isHover ?
											attributes.capacitiesItemColorHover
											: attributes.capacitiesItemColor}
										defaultColor={isHover ? '' : ''}
										onChange={(value) => {
											const hex = (value && typeof value === 'object') ? value.hex : value;
											setAttributes({ [isHover ? 'capacitiesItemColorHover' : 'capacitiesItemColor']: hex });
										}}
									/>
								</div>
							);
						}}
					</TabPanel>
					<Divider />
					<BoxControl
						label={__('Wrapper Padding', 'easy-hotel')}
						values={attributes.capacitiesWrapperPadding}
						onChange={(value) => setAttributes({ capacitiesWrapperPadding: value })}
					/>
					<Divider />
					<BoxControl
						label={__('Wrapper Margin', 'easy-hotel')}
						values={attributes.capacitiesWrapperMargin}
						onChange={(value) => setAttributes({ capacitiesWrapperMargin: value })}
					/>
					<Divider />
					<BoxControl
						label={__('Item Padding', 'easy-hotel')}
						values={attributes.capacitiesItemPadding}
						onChange={(value) => setAttributes({ capacitiesItemPadding: value })}
					/>
					<Divider />
					<BoxControl
						label={__('Item Margin', 'easy-hotel')}
						values={attributes.capacitiesItemMargin}
						onChange={(value) => setAttributes({ capacitiesItemMargin: value })}
					/>
					<Divider />
					<TypographyControls
						label={__('Typography', 'easy-hotel')}
						attributes={attributes}
						setAttributes={setAttributes}
						attributeKey="capacitiesItemTypography"
					/>
				</PanelBody>

				<PanelBody title={__('Pricing', 'easy-hotel')} initialOpen={false}>
					<TabPanel
						className="eshb-tab-panel"
						activeClass="is-active"
						tabs={[
							{ name: 'normal', title: __('Normal', 'easy-hotel'), className: 'eshb-tab-normal' },
							{ name: 'hover', title: __('Hover', 'easy-hotel'), className: 'eshb-tab-hover' },
						]}
					>
						{(tab) => {
							const isHover = tab.name === 'hover';
							return (
								<div style={{ marginTop: '15px' }}>
									<ColorPopover
										label={isHover ? __('Color (Hover)', 'easy-hotel') : __('Color', 'easy-hotel')}
										color={isHover ?
											attributes.itemPricingColorHover
											: attributes.itemPricingColor}
										defaultColor={isHover ? '' : ''}
										onChange={(value) => {
											const hex = (value && typeof value === 'object') ? value.hex : value;
											setAttributes({ [isHover ? 'itemPricingColorHover' : 'itemPricingColor']: hex });
										}}
									/>
								</div>
							);
						}}
					</TabPanel>
					<Divider />
					<BoxControl
						label={__('Padding', 'easy-hotel')}
						values={attributes.itemPricingPadding}
						onChange={(value) => setAttributes({ itemPricingPadding: value })}
					/>
					<Divider />
					<BoxControl
						label={__('Margin', 'easy-hotel')}
						values={attributes.itemPricingMargin}
						onChange={(value) => setAttributes({ itemPricingMargin: value })}
					/>
					<Divider />
					<TypographyControls
						label={__('Typography', 'easy-hotel')}
						attributes={attributes}
						setAttributes={setAttributes}
						attributeKey="itemPricingTypography"
					/>
					<Divider />
					<div className="eshb-divider-label">{__('Periodicity', 'easy-hotel')}</div>
					<TabPanel
						className="eshb-tab-panel"
						activeClass="is-active"
						tabs={[
							{ name: 'normal', title: __('Normal', 'easy-hotel'), className: 'eshb-tab-normal' },
							{ name: 'hover', title: __('Hover', 'easy-hotel'), className: 'eshb-tab-hover' },
						]}
					>
						{(tab) => {
							const isHover = tab.name === 'hover';
							return (
								<div style={{ marginTop: '15px' }}>
									<ColorPopover
										label={isHover ? __('Color (Hover)', 'easy-hotel') : __('Color', 'easy-hotel')}
										color={isHover ?
											attributes.itemPricingPerodicityColorHover
											: attributes.itemPricingPerodicityColor}
										defaultColor={isHover ? '' : ''}
										onChange={(value) => {
											const hex = (value && typeof value === 'object') ? value.hex : value;
											setAttributes({ [isHover ? 'itemPricingPerodicityColorHover' : 'itemPricingPerodicityColor']: hex });
										}}
									/>
								</div>
							);
						}}
					</TabPanel>
					<TypographyControls
						label={__('Typography', 'easy-hotel')}
						attributes={attributes}
						setAttributes={setAttributes}
						attributeKey="itemPricingPerodicityTypography"
					/>
				</PanelBody>

				<PanelBody title={__('Button', 'easy-hotel')} initialOpen={false}>
					<TabPanel
						className="eshb-tab-panel"
						activeClass="is-active"
						tabs={[
							{ name: 'normal', title: __('Normal', 'easy-hotel'), className: 'eshb-tab-normal' },
							{ name: 'hover', title: __('Hover', 'easy-hotel'), className: 'eshb-tab-hover' },
						]}
					>
						{(tab) => {
							const isHover = tab.name === 'hover';
							return (
								<div style={{ marginTop: '15px' }}>
									<BackgroundControl
										label={isHover ? __('Background (Hover)', 'easy-hotel') : __('Background', 'easy-hotel')}
										colorValue={isHover ? attributes.itemButtonBackgroundColorHover : attributes.itemButtonBackgroundColor}
										gradientValue={isHover ? attributes.itemButtonBackgroundGradientHover : attributes.itemButtonBackgroundGradient}
										onColorChange={(value) => {
											const hex = (value && typeof value === 'object') ? value.hex : value;
											setAttributes({ [isHover ? 'itemButtonBackgroundColorHover' : 'itemButtonBackgroundColor']: hex });
										}}
										onGradientChange={(value) => setAttributes({ [isHover ? 'itemButtonBackgroundGradientHover' : 'itemButtonBackgroundGradient']: value })}
									/>
									<ColorPopover
										label={isHover ? __('Color (Hover)', 'easy-hotel') : __('Color', 'easy-hotel')}
										color={isHover ?
											attributes.itemButtonColorHover
											: attributes.itemButtonColor}
										defaultColor={isHover ? '' : ''}
										onChange={(value) => {
											const hex = (value && typeof value === 'object') ? value.hex : value;
											setAttributes({ [isHover ? 'itemButtonColorHover' : 'itemButtonColor']: hex });
										}}
									/>
								</div>
							);
						}}
					</TabPanel>
					<Divider />
					<BoxControl
						label={__('Padding', 'easy-hotel')}
						values={attributes.itemButtonPadding}
						onChange={(value) => setAttributes({ itemButtonPadding: value })}
					/>
					<Divider />
					<BoxControl
						label={__('Margin', 'easy-hotel')}
						values={attributes.itemButtonMargin}
						onChange={(value) => setAttributes({ itemButtonMargin: value })}
					/>
					<Divider />
					<TypographyControls
						label={__('Typography', 'easy-hotel')}
						attributes={attributes}
						setAttributes={setAttributes}
						attributeKey="itemButtonTypography"
					/>
				</PanelBody>

			</InspectorControls>
			<ServerSideRender
				block={metadata.name}
				attributes={attributes}
			/>
		</div>
	);
}
