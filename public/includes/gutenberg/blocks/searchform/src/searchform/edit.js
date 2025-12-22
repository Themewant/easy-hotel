import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
    PanelBody,
    __experimentalHeading as Heading,
    BoxControl,
    __experimentalDivider as Divider,
    TabPanel
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';
import './editor.scss';
import BoxShadowControls from './custom-components/BoxShadowControls';
import TypographyControls from './custom-components/TypographyControls';
import ColorPopover from './custom-components/ColorPopover';

export default function Edit({ attributes, setAttributes }) {
    const {
        customBackgroundColor,
        padding,
        fieldGroupPadding,
        fieldGroupMargin,
        fieldLabelColor,
        fieldTextColor,
        margin,
        borderRadius,
        plusMinusBtnBackgroundColor,
        plusMinusBtnTextColor,
        plusMinusBtnPadding,
        customBackgroundColorHover,
        fieldLabelColorHover,
        fieldTextColorHover
    } = attributes;

    return (
        <div {...useBlockProps()}>
            <InspectorControls group="styles">
                <PanelBody title={__('Container', 'easy-hotel')} initialOpen={false}>
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
                                        label={isHover ? __('Background (Hover)', 'easy-hotel') : __('Background Color', 'easy-hotel')}
                                        color={isHover ? customBackgroundColorHover : customBackgroundColor}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'customBackgroundColorHover' : 'customBackgroundColor']: hex });
                                        }}
                                    />
                                    <BoxShadowControls
                                        attributes={attributes}
                                        setAttributes={setAttributes}
                                        state={tab.name}
                                    />
                                </div>
                            );
                        }}
                    </TabPanel>

                    <Divider />
                    <BoxControl
                        label={__('Padding', 'easy-hotel')}
                        values={padding}
                        onChange={(nextValues) => setAttributes({ padding: nextValues })}
                    />
                    <BoxControl
                        label={__('Margin', 'easy-hotel')}
                        values={margin}
                        onChange={(nextValues) => setAttributes({ margin: nextValues })}
                    />
                    <Divider />

                    {/* Border Radious*/}
                    <BoxControl
                        label={__('Border Radious', 'easy-hotel')}
                        values={borderRadius}
                        onChange={(nextValues) => setAttributes({ borderRadius: nextValues })}
                    />
                    <Divider />
                </PanelBody>

                <PanelBody title={__('Fields Group', 'easy-hotel')} initialOpen={false}>
                    <BoxControl
                        label={__('Padding', 'easy-hotel')}
                        values={fieldGroupPadding}
                        onChange={(nextValues) => setAttributes({ fieldGroupPadding: nextValues })}
                    />
                    <BoxControl
                        label={__('Margin', 'easy-hotel')}
                        values={fieldGroupMargin}
                        onChange={(nextValues) => setAttributes({ fieldGroupMargin: nextValues })}
                    />
                    <Divider />
                </PanelBody>

                <PanelBody title={__('Fields', 'easy-hotel')} initialOpen={false}>
                    <Heading level={2}>{__('Label', 'easy-hotel')}</Heading>
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
                                        label={isHover ? __('Color (Hover)', 'easy-hotel') : __('Color', 'easy-hotel')}
                                        color={isHover ? fieldLabelColorHover : fieldLabelColor}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'fieldLabelColorHover' : 'fieldLabelColor']: hex });
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
                        attributeKey="fieldLabelTypography"
                        nextDefaultSize={true}
                    />

                    <Divider />

                    <Heading level={2}>{__('Input', 'easy-hotel')}</Heading>
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
                                        label={isHover ? __('Color (Hover)', 'easy-hotel') : __('Color', 'easy-hotel')}
                                        color={isHover ? fieldTextColorHover : fieldTextColor}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'fieldTextColorHover' : 'fieldTextColor']: hex });
                                        }}
                                    />
                                </div>
                            );
                        }}
                    </TabPanel>
                    <TypographyControls
                        label={__('Field Text Typography', 'easy-hotel')}
                        attributes={attributes}
                        setAttributes={setAttributes}
                        attributeKey="fieldTextTypography"
                        nextDefaultSize={true}
                    />
                </PanelBody>

                <PanelBody title={__('Plus Minus Button', 'easy-hotel')} initialOpen={false}>
                    {/* Add Plus Minus Button styles here */}
                    <ColorPopover
                        label={__('Background Color', 'easy-hotel')}
                        color={plusMinusBtnBackgroundColor}
                        onChange={(value) => {
                            if (value && typeof value === 'object') {
                                setAttributes({ plusMinusBtnBackgroundColor: value.hex });
                            } else {
                                setAttributes({ plusMinusBtnBackgroundColor: value });
                            }
                        }}
                    />
                    <ColorPopover
                        label={__('Color', 'easy-hotel')}
                        color={plusMinusBtnTextColor}
                        onChange={(value) => {
                            if (value && typeof value === 'object') {
                                setAttributes({ plusMinusBtnTextColor: value.hex });
                            } else {
                                setAttributes({ plusMinusBtnTextColor: value });
                            }
                        }}
                    />
                    <TypographyControls
                        label={__('Typography', 'easy-hotel')}
                        attributes={attributes}
                        setAttributes={setAttributes}
                        attributeKey="plusMinusBtnTypography"
                        nextDefaultSize={true}
                    />
                    <BoxControl
                        label={__('Padding', 'easy-hotel')}
                        values={plusMinusBtnPadding}
                        onChange={(nextValues) => setAttributes({ plusMinusBtnPadding: nextValues })}
                    />
                </PanelBody>

                <PanelBody title={__('Button', 'easy-hotel')} initialOpen={false}>
                    {/* Add Button styles here */}
                </PanelBody>
            </InspectorControls>
            <ServerSideRender
                block={metadata.name}
                attributes={attributes}
            />
        </div>
    );
}
