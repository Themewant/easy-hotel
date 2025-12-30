import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
    PanelBody,
    __experimentalHeading as Heading,
    BoxControl,
    __experimentalDivider as Divider,
    TabPanel,
    SelectControl,
    TextControl,
    ToggleControl,
    __experimentalNumberControl as NumberControl
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';
import './editor.scss';
import ColorPopover from '../../../custom-components/ColorPopover';

export default function Edit({ attributes, setAttributes }) {
    return (
        <div {...useBlockProps()}>
            <InspectorControls>
                <PanelBody title={__('Slides Per View', 'easy-hotel')} initialOpen={true}>
                    <SelectControl
                        label={__('Slides Per View', 'easy-hotel')}
                        value={attributes.slidesPerView}
                        options={[
                            { label: __('1', 'easy-hotel'), value: 1 },
                            { label: __('2', 'easy-hotel'), value: 2 },
                            { label: __('2.1', 'easy-hotel'), value: 2.1 },
                            { label: __('3', 'easy-hotel'), value: 3 },
                            { label: __('3.1', 'easy-hotel'), value: 3.1 },
                            { label: __('4', 'easy-hotel'), value: 4 },
                            { label: __('4.1', 'easy-hotel'), value: 4.1 }
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
                    <Divider />
                    <ToggleControl
                        __nextHasNoMarginBottom={true}
                        label={__('Centered Slides', 'easy-hotel')}
                        help={
                            attributes.centeredSlides
                                ? __('Centered Slides', 'easy-hotel')
                                : __('No centered slides', 'easy-hotel')
                        }
                        checked={attributes.centeredSlides}
                        onChange={(newValue) => {
                            setAttributes({ centeredSlides: newValue });
                        }}
                    />

                </PanelBody>
            </InspectorControls>
            <InspectorControls group="styles">
                <PanelBody title={__('Item', 'easy-hotel')} initialOpen={false}>
                    <TextControl
                        __nextHasNoMarginBottom={true}
                        __next40pxDefaultSize={true}
                        label={__('Item Gap', 'easy-hotel')}
                        value={attributes.spaceBetween}
                        onChange={(value) => setAttributes({ spaceBetween: value })}
                    />
                    <Divider />
                    <BoxControl
                        __nextHasNoMarginBottom={true}
                        __next40pxDefaultSize={true}
                        label={__('Border Radious', 'easy-hotel')}
                        values={attributes.itemBorderRadius}
                        onChange={(nextValues) => setAttributes({ itemBorderRadius: nextValues })}
                    />
                </PanelBody>

                <PanelBody title={__('Navigation Button', 'easy-hotel')} initialOpen={false}>
                    <TabPanel
                        className="eshb-tab-panel"
                        tabs={[
                            { name: 'normal', title: __('Normal', 'easy-hotel') },
                            { name: 'hover', title: __('Hover', 'easy-hotel') },
                        ]}
                    >
                        {(tab) => {
                            const isHover = tab.name === 'hover';
                            return (
                                <div style={{ marginTop: '15px' }}>
                                    <ColorPopover
                                        label={isHover ? __('Background (Hover)', 'easy-hotel') : __('Background Color', 'easy-hotel')}
                                        color={isHover ? attributes.navBtnBgColor : attributes.navBtnBgColor}
                                        defaultColor={isHover ? '' : 'var(--eshb-primary-color)'}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'navBtnBgColor' : 'navBtnBgColor']: hex });
                                        }}
                                    />
                                    <ColorPopover
                                        label={isHover ? __('Color (Hover)', 'easy-hotel') : __('Color', 'easy-hotel')}
                                        color={isHover ? attributes.navBtnColorHover : attributes.navBtnColor}
                                        defaultColor={isHover ? '' : 'var(--eshb-white-color)'}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'navBtnColorHover' : 'navBtnColor']: hex });
                                        }}
                                    />
                                </div>
                            );
                        }}
                    </TabPanel>
                    <Divider />
                    <BoxControl
                        __nextHasNoMarginBottom={true}
                        __next40pxDefaultSize={true}
                        label={__('Padding', 'easy-hotel')}
                        values={attributes.navBtnPadding}
                        onChange={(nextValues) => setAttributes({ navBtnPadding: nextValues })}
                    />
                    <BoxControl
                        __nextHasNoMarginBottom={true}
                        __next40pxDefaultSize={true}
                        label={__('Next Button Border Radius', 'easy-hotel')}
                        values={attributes.nextBtnBorderRadius}
                        onChange={(nextValues) => setAttributes({ nextBtnBorderRadius: nextValues })}
                    />
                    <BoxControl
                        __nextHasNoMarginBottom={true}
                        __next40pxDefaultSize={true}
                        label={__('Previous Button Border Radius', 'easy-hotel')}
                        values={attributes.prevBtnBorderRadius}
                        onChange={(nextValues) => setAttributes({ prevBtnBorderRadius: nextValues })}
                    />
                </PanelBody>
                <PanelBody title={__('Navigation Dots', 'easy-hotel')} initialOpen={false}>
                    <TabPanel
                        className="eshb-tab-panel"
                        tabs={[
                            { name: 'normal', title: __('Normal', 'easy-hotel') },
                            { name: 'hover', title: __('Hover', 'easy-hotel') },
                        ]}
                    >
                        {(tab) => {
                            const isHover = tab.name === 'hover';
                            return (
                                <div style={{ marginTop: '15px' }}>
                                    <ColorPopover
                                        label={isHover ? __('Background (Hover)', 'easy-hotel') : __('Background Color', 'easy-hotel')}
                                        color={isHover ? attributes.dotsBgColorHover : attributes.dotsBgColor}
                                        defaultColor={isHover ? '' : 'var(--eshb-primary-color)'}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'dotsBgColorHover' : 'dotsBgColor']: hex });
                                        }}
                                    />
                                    <ColorPopover
                                        label={isHover ? __('Color (Hover)', 'easy-hotel') : __('Color', 'easy-hotel')}
                                        color={isHover ? attributes.dotsColorHover : attributes.dotsColor}
                                        defaultColor={isHover ? '' : 'var(--eshb-white-color)'}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'dotsColorHover' : 'dotsColor']: hex });
                                        }}
                                    />
                                </div>
                            );
                        }}
                    </TabPanel>
                    <Divider />
                    <NumberControl
                        __nextHasNoMarginBottom={true}
                        __next40pxDefaultSize={true}
                        label={__('Size (px)', 'easy-hotel')}
                        value={attributes.dotsSize}
                        onChange={(nextValue) => setAttributes({ dotsSize: nextValue })}
                    />
                    <BoxControl
                        __nextHasNoMarginBottom={true}
                        __next40pxDefaultSize={true}
                        label={__('Border Radius', 'easy-hotel')}
                        values={attributes.dotsBorderRadius}
                        onChange={(nextValues) => setAttributes({ dotsBorderRadius: nextValues })}
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
