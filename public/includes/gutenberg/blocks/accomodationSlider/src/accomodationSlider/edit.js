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
	BoxControl,
	__experimentalDivider as Divider,
	TabPanel,
	__experimentalNumberControl as NumberControl,
	SelectControl,
	TextControl,
	ToggleControl
} from '@wordpress/components';
import BackgroundControl from '../../../custom-components/BackgroundControl';
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

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				{/* {query settings panel group} */}
				<PanelBody title={__('Query', 'easy-hotel')} initialOpen={false}>
					<ToggleControl
						label={__('Show Related Posts', 'easy-hotel')}
						checked={attributes.is_related_post}
						onChange={(value) => setAttributes({ is_related_post: value })}
					/>
					<NumberControl
						label={__('Posts Per Page', 'easy-hotel')}
						value={attributes.per_page}
						onChange={(value) => setAttributes({ per_page: value })}
					/>
					<SelectControl
						label={__('Order', 'easy-hotel')}
						value={attributes.room_order}
						onChange={(value) => setAttributes({ room_order: value })}
						options={[
							{ label: __('Ascending', 'easy-hotel'), value: 'ASC' },
							{ label: __('Descending', 'easy-hotel'), value: 'DESC' },
						]}
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
					/>
				</PanelBody>
				{ /* content settings panel group */}
				<PanelBody title={__('Content', 'easy-hotel')} initialOpen={false}>
					<SelectControl
						label={__('Style', 'easy-hotel')}
						value={attributes.grid_style}
						onChange={(value) => setAttributes({ grid_style: value })}
						options={[
							{ label: __('Style 1', 'easy-hotel'), value: '1' },
							{ label: __('Style 2', 'easy-hotel'), value: '2' },
							{ label: __('Style 3', 'easy-hotel'), value: '3' },
						]}
					/>
					<TextControl
						label={__('Button Text', 'easy-hotel')}
						value={attributes.btn_text}
						onChange={(value) => setAttributes({ btn_text: value })}
					/>
				</PanelBody>

			</InspectorControls>
			<InspectorControls>
				<PanelBody title={__('Slider Settings', 'easy-hotel')} initialOpen={true}>
					<SelectControl
						label={__('Slides Per View', 'easy-hotel')}
						value={attributes.slidesPerView}
						options={[
							{ label: __('1', 'easy-hotel'), value: 1 },
							{ label: __('2', 'easy-hotel'), value: 2 },
							{ label: __('2.3', 'easy-hotel'), value: 2.3 },
							{ label: __('3', 'easy-hotel'), value: 3 },
							{ label: __('3.3', 'easy-hotel'), value: 3.3 },
							{ label: __('4', 'easy-hotel'), value: 4 },
							{ label: __('4.3', 'easy-hotel'), value: 4.3 }
						]}
						onChange={(value) => setAttributes({ slidesPerView: value })}
						help={__('Choose which effect this booking form is for', 'easy-hotel')}
						__next40pxDefaultSize={true}
						__nextHasNoMarginBottom={true}
					/>
					<SelectControl
						label={__('Slides Per View Tablet', 'easy-hotel')}
						value={attributes.slidesPerViewTablet}
						options={[
							{ label: __('1', 'easy-hotel'), value: 1 },
							{ label: __('2', 'easy-hotel'), value: 2 },
							{ label: __('2.3', 'easy-hotel'), value: 2.3 },
							{ label: __('3', 'easy-hotel'), value: 3 },
							{ label: __('3.3', 'easy-hotel'), value: 3.3 },
							{ label: __('4', 'easy-hotel'), value: 4 },
							{ label: __('4.3', 'easy-hotel'), value: 4.3 }
						]}
						onChange={(value) => setAttributes({ slidesPerViewTablet: value })}
						help={__('Choose which effect this booking form is for', 'easy-hotel')}
						__next40pxDefaultSize={true}
						__nextHasNoMarginBottom={true}
					/>
					<SelectControl
						label={__('Slides Per View Mobile', 'easy-hotel')}
						value={attributes.slidesPerViewMobile}
						options={[
							{ label: __('1', 'easy-hotel'), value: 1 },
							{ label: __('2', 'easy-hotel'), value: 2 },
							{ label: __('2.3', 'easy-hotel'), value: 2.3 },
							{ label: __('3', 'easy-hotel'), value: 3 },
							{ label: __('3.3', 'easy-hotel'), value: 3.3 },
							{ label: __('4', 'easy-hotel'), value: 4 },
							{ label: __('4.3', 'easy-hotel'), value: 4.3 }
						]}
						onChange={(value) => setAttributes({ slidesPerViewMobile: value })}
						help={__('Choose which effect this booking form is for', 'easy-hotel')}
						__next40pxDefaultSize={true}
						__nextHasNoMarginBottom={true}
					/>
					<SelectControl
						label={__('Slides Per View Mobile Small', 'easy-hotel')}
						value={attributes.slidesPerViewMobileSmall}
						options={[
							{ label: __('1', 'easy-hotel'), value: 1 },
							{ label: __('2', 'easy-hotel'), value: 2 },
							{ label: __('2.3', 'easy-hotel'), value: 2.3 },
							{ label: __('3', 'easy-hotel'), value: 3 },
							{ label: __('3.3', 'easy-hotel'), value: 3.3 },
							{ label: __('4', 'easy-hotel'), value: 4 },
							{ label: __('4.3', 'easy-hotel'), value: 4.3 }
						]}
						onChange={(value) => setAttributes({ slidesPerViewMobileSmall: value })}
						help={__('Choose which effect this booking form is for', 'easy-hotel')}
						__next40pxDefaultSize={true}
						__nextHasNoMarginBottom={true}
					/>
					<SelectControl
						label={__('Slides To Scroll', 'easy-hotel')}
						value={attributes.slidesToScroll}
						options={[
							{ label: __('1', 'easy-hotel'), value: 1 },
							{ label: __('2', 'easy-hotel'), value: 2 },
							{ label: __('2.3', 'easy-hotel'), value: 2.3 },
							{ label: __('3', 'easy-hotel'), value: 3 },
							{ label: __('3.3', 'easy-hotel'), value: 3.3 },
							{ label: __('4', 'easy-hotel'), value: 4 },
							{ label: __('4.3', 'easy-hotel'), value: 4.3 }
						]}
						onChange={(value) => setAttributes({ slidesToScroll: value })}
						help={__('Choose which effect this booking form is for', 'easy-hotel')}
						__next40pxDefaultSize={true}
						__nextHasNoMarginBottom={true}
					/>
					<SelectControl
						label={__('Select effect', 'easy-hotel')}
						value={attributes.effect}
						options={[
							{ label: __('Slide', 'easy-hotel'), value: 'slide' },
							{ label: __('Fade', 'easy-hotel'), value: 'fade' },
							{ label: __('Flip', 'easy-hotel'), value: 'flip' },
							{ label: __('Cube', 'easy-hotel'), value: 'cube' },
							{ label: __('Coverflow', 'easy-hotel'), value: 'coverflow' },
							{ label: __('Cards', 'easy-hotel'), value: 'cards' },
							{ label: __('Creative', 'easy-hotel'), value: 'creative' }
						]}
						onChange={(value) => setAttributes({ effect: value })}
						help={__('Choose which effect this booking form is for', 'easy-hotel')}
						__next40pxDefaultSize={true}
						__nextHasNoMarginBottom={true}
					/>
					<Divider />
					<ToggleControl
						__nextHasNoMarginBottom={true}
						label={__('Loop', 'easy-hotel')}
						help={attributes.loop ? __('Loop', 'easy-hotel') : __('No loop', 'easy-hotel')}
						checked={attributes.loop}
						onChange={(newValue) => {
							setAttributes({ loop: newValue });
						}}
					/>
					<Divider />
					<ToggleControl
						__nextHasNoMarginBottom={true}
						label={__('Autoplay', 'easy-hotel')}
						help={attributes.autoplay ? __('Autoplay', 'easy-hotel') : __('No autoplay', 'easy-hotel')}
						checked={attributes.autoplay}
						onChange={(newValue) => {
							setAttributes({ autoplay: newValue });
						}}
					/>
					<Divider />
					<TextControl
						__next40pxDefaultSize={true}
						__nextHasNoMarginBottom={true}
						label={__('Speed', 'easy-hotel')}
						value={attributes.speed}
						onChange={(value) => setAttributes({ speed: value })}
						help={__('Speed of the transition between slides', 'easy-hotel')}
					/>
					<Divider />
					<TextControl
						__next40pxDefaultSize={true}
						__nextHasNoMarginBottom={true}
						label={__('Autoplay Speed', 'easy-hotel')}
						value={attributes.autoplaySpeed}
						onChange={(value) => setAttributes({ autoplaySpeed: value })}
						help={__('Autoplay speed of the transition between slides', 'easy-hotel')}
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
