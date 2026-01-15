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
import BoxShadowControls from '../../../custom-components/BoxShadowControls';
import TypographyControls from '../../../custom-components/TypographyControls';
import ColorPopover from '../../../custom-components/ColorPopover';

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
        fieldTextColorHover,
        plusMinusBtnBackgroundColorHover,
        plusMinusBtnTextColorHover,
        submitBtnBackgroundColor,
        submitBtnTextColor,
        submitBtnBackgroundColorHover,
        submitBtnTextColorHover,
        submitBtnPadding,
        submitBtnMargin
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
                                        defaultColor={isHover ? '' : 'var(--eshb-dark-color)'}
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
                    <Divider />
                    <BoxControl
                        label={__('Margin', 'easy-hotel')}
                        values={margin}
                        onChange={(nextValues) => setAttributes({ margin: nextValues })}
                    />
                    <Divider />
                    <BoxControl
                        label={__('Border Radious', 'easy-hotel')}
                        values={borderRadius}
                        onChange={(nextValues) => setAttributes({ borderRadius: nextValues })}
                    />
                </PanelBody>

                <PanelBody title={__('Fields Group', 'easy-hotel')} initialOpen={false}>
                    <BoxControl
                        label={__('Padding', 'easy-hotel')}
                        values={fieldGroupPadding}
                        onChange={(nextValues) => setAttributes({ fieldGroupPadding: nextValues })}
                    />
                    <Divider />
                    <BoxControl
                        label={__('Margin', 'easy-hotel')}
                        values={fieldGroupMargin}
                        onChange={(nextValues) => setAttributes({ fieldGroupMargin: nextValues })}
                    />
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
                                        defaultColor={isHover ? '' : 'var(--eshb-primary-color)'}
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
                                        defaultColor={isHover ? '' : 'var(--eshb-white-color)'}
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
                                        color={isHover ? plusMinusBtnBackgroundColorHover : plusMinusBtnBackgroundColor}
                                        defaultColor={isHover ? '' : 'var(--eshb-primary-color)'}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'plusMinusBtnBackgroundColorHover' : 'plusMinusBtnBackgroundColor']: hex });
                                        }}
                                    />
                                    <ColorPopover
                                        label={isHover ? __('Color (Hover)', 'easy-hotel') : __('Color', 'easy-hotel')}
                                        color={isHover ? plusMinusBtnTextColorHover : plusMinusBtnTextColor}
                                        defaultColor={isHover ? '' : 'var(--eshb-white-color)'}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'plusMinusBtnTextColorHover' : 'plusMinusBtnTextColor']: hex });
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
                        attributeKey="plusMinusBtnTypography"
                        nextDefaultSize={true}
                    />
                    <Divider />
                    <BoxControl
                        label={__('Padding', 'easy-hotel')}
                        values={plusMinusBtnPadding}
                        onChange={(nextValues) => setAttributes({ plusMinusBtnPadding: nextValues })}
                    />
                </PanelBody>

                <PanelBody title={__('Submit Button', 'easy-hotel')} initialOpen={false}>
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
                                        color={isHover ? submitBtnBackgroundColorHover : submitBtnBackgroundColor}
                                        defaultColor={isHover ? '' : 'var(--eshb-primary-color)'}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'submitBtnBackgroundColorHover' : 'submitBtnBackgroundColor']: hex });
                                        }}
                                    />
                                    <ColorPopover
                                        label={isHover ? __('Color (Hover)', 'easy-hotel') : __('Color', 'easy-hotel')}
                                        color={isHover ? submitBtnTextColorHover : submitBtnTextColor}
                                        defaultColor={isHover ? '' : 'var(--eshb-white-color)'}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'submitBtnTextColorHover' : 'submitBtnTextColor']: hex });
                                        }}
                                    />
                                </div>
                            );
                        }}
                    </TabPanel>
                    <Divider />
                    <BoxControl
                        label={__('Padding', 'easy-hotel')}
                        values={submitBtnPadding}
                        onChange={(nextValues) => setAttributes({ submitBtnPadding: nextValues })}
                    />
                    <Divider />
                    <BoxControl
                        label={__('Margin', 'easy-hotel')}
                        values={submitBtnMargin}
                        onChange={(nextValues) => setAttributes({ submitBtnMargin: nextValues })}
                    />
                    <Divider />
                    <TypographyControls
                        label={__('Typography', 'easy-hotel')}
                        attributes={attributes}
                        setAttributes={setAttributes}
                        attributeKey="submitBtnTypography"
                        nextDefaultSize={true}
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
