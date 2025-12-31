import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
    PanelBody,
    __experimentalDivider as Divider,
    TabPanel,
    TextControl,
    __experimentalNumberControl as NumberControl
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';
import './editor.scss';
import ColorPopover from '../../../custom-components/ColorPopover';

export default function Edit({ attributes, setAttributes }) {
    return (
        <div {...useBlockProps()}>
            <InspectorControls group="styles">
                <PanelBody title={__('Item', 'easy-hotel')} initialOpen={false}>
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
                                        label={isHover ? __('Text Color (Hover)', 'easy-hotel') : __('Text Color', 'easy-hotel')}
                                        color={isHover ? attributes.textColorHover : attributes.textColor}
                                        defaultColor={isHover ? '' : ''}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'textColorHover' : 'textColor']: hex });
                                        }}
                                    />
                                    <ColorPopover
                                        label={isHover ? __('Icon Color (Hover)', 'easy-hotel') : __('Icon Color', 'easy-hotel')}
                                        color={isHover ? attributes.iconColorHover : attributes.iconColor}
                                        defaultColor={isHover ? '' : ''}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'iconColorHover' : 'iconColor']: hex });
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
                        label={__('Item Gap (px)', 'easy-hotel')}
                        value={attributes.spaceBetween}
                        onChange={(value) => setAttributes({ spaceBetween: value })}
                    />
                    <NumberControl
                        __nextHasNoMarginBottom={true}
                        __next40pxDefaultSize={true}
                        label={__('Text Size (px)', 'easy-hotel')}
                        value={attributes.textSize}
                        onChange={(nextValue) => setAttributes({ textSize: nextValue })}
                    />
                    <NumberControl
                        __nextHasNoMarginBottom={true}
                        __next40pxDefaultSize={true}
                        label={__('Icon Size (px)', 'easy-hotel')}
                        value={attributes.iconSize}
                        onChange={(nextValue) => setAttributes({ iconSize: nextValue })}
                    />
                    <NumberControl
                        __nextHasNoMarginBottom={true}
                        __next40pxDefaultSize={true}
                        label={__('Icon Space (px)', 'easy-hotel')}
                        value={attributes.iconSpace}
                        onChange={(nextValue) => setAttributes({ iconSpace: nextValue })}
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
