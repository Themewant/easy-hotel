import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
    PanelBody,
    __experimentalHeading as Heading,
    BoxControl,
    __experimentalDivider as Divider,
    TabPanel,
    SelectControl
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';
import './editor.scss';
import TypographyControls from '../../../custom-components/TypographyControls';
import ColorPopover from '../../../custom-components/ColorPopover';
import BackgroundControl from '../../../custom-components/BackgroundControl';

export default function Edit({ attributes, setAttributes }) {
    const {
        customBackgroundColor,
        padding,
        fieldGroupPadding,
        fieldGroupMargin,
        fieldLabelColor,
        fieldTextColor,
        fieldBorderRadius,
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
        submitBtnMargin,
        submitBtnBorderRadius,
        extraServicesColor,
        extraServicesColorHover,
        extraServicesMargin,
        groupTitleColor,
        groupTitleColorHover,
        formTitleColor,
        formTitleColorHover,
        serviceCheckboxBorderRadius,
        serviceQtyBorderRadius,
    } = attributes;

    // Fetch accomodations
    const accomodations = useSelect((select) => {
        return select('core').getEntityRecords('postType', 'eshb_accomodation', {
            per_page: -1,
            status: 'publish',
            orderby: 'title',
            order: 'asc'
        });
    }, []);

    return (
        <div {...useBlockProps()}>
            <InspectorControls>
                <PanelBody title={__('accomodation', 'easy-hotel')} initialOpen={true}>
                    <SelectControl
                        label={__('Select accomodation', 'easy-hotel')}
                        value={attributes.accomodationId}
                        options={[
                            { label: __('-- Select accomodation --', 'easy-hotel'), value: '' },
                            ...(accomodations || []).map((accomodation) => ({
                                label: accomodation.title.rendered,
                                value: accomodation.id.toString()
                            }))
                        ]}
                        onChange={(value) => setAttributes({ accomodationId: value })}
                        help={__('Choose which accomodation this booking form is for', 'easy-hotel')}
                        __next40pxDefaultSize={true}
                        __nextHasNoMarginBottom={true}
                    />
                </PanelBody>
            </InspectorControls>
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
                                    <BackgroundControl
                                        label={isHover ? __('Background (Hover)', 'easy-hotel') : __('Background', 'easy-hotel')}
                                        colorValue={isHover ? attributes.customBackgroundColorHover : attributes.customBackgroundColor}
                                        gradientValue={isHover ? attributes.customBackgroundGradientHover : attributes.customBackgroundGradient}
                                        onColorChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'customBackgroundColorHover' : 'customBackgroundColor']: hex });
                                        }}
                                        onGradientChange={(value) => setAttributes({ [isHover ? 'customBackgroundGradientHover' : 'customBackgroundGradient']: value })}
                                    />
                                </div>
                            );
                        }}
                    </TabPanel>
                    <Divider />
                    <BoxControl
                        label={__('Padding', 'easy-hotel')}
                        values={attributes.padding}
                        onChange={(value) => setAttributes({ padding: value })}
                    />
                    <Divider />
                    <BoxControl
                        label={__('Border Radious', 'easy-hotel')}
                        values={attributes.borderRadius}
                        onChange={(nextValues) => setAttributes({ borderRadius: nextValues })}
                    />
                </PanelBody>
                <PanelBody title={__('Form Title', 'easy-hotel')} initialOpen={false}>
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
                                        color={isHover ? formTitleColorHover : formTitleColor}
                                        defaultColor={isHover ? '' : 'var(--eshb-white-color)'}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'formTitleColorHover' : 'formTitleColor']: hex });
                                        }}
                                    />
                                </div>
                            );
                        }}
                    </TabPanel>
                </PanelBody>
                <PanelBody title={__('Fields Group', 'easy-hotel')} initialOpen={false}>
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
                                        color={isHover ? groupTitleColorHover : groupTitleColor}
                                        defaultColor={isHover ? '' : 'var(--eshb-primary-color)'}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'groupTitleColorHover' : 'groupTitleColor']: hex });
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
                        attributeKey="groupTitleTypography"
                        nextDefaultSize={true}
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
                                        defaultColor={isHover ? '' : ''}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'fieldTextColorHover' : 'fieldTextColor']: hex });
                                        }}
                                    />
                                </div>
                            );
                        }}
                    </TabPanel>
                    <BoxControl
                        label={__('Border Radious', 'easy-hotel')}
                        values={fieldBorderRadius}
                        onChange={(nextValues) => setAttributes({ fieldBorderRadius: nextValues })}
                    />
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
                    <Divider />
                    <BoxControl
                        label={__('Padding', 'easy-hotel')}
                        values={plusMinusBtnPadding}
                        onChange={(nextValues) => setAttributes({ plusMinusBtnPadding: nextValues })}
                    />
                </PanelBody>

                <PanelBody title={__('Extra Services', 'easy-hotel')} initialOpen={false}>
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
                                        color={isHover ? extraServicesColorHover : extraServicesColor}
                                        defaultColor={isHover ? '' : ''}
                                        onChange={(value) => {
                                            const hex = (value && typeof value === 'object') ? value.hex : value;
                                            setAttributes({ [isHover ? 'extraServicesColorHover' : 'extraServicesColor']: hex });
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
                        attributeKey="extraServicesTypography"
                        nextDefaultSize={true}
                    />
                    <Divider />
                    <BoxControl
                        label={__('Margin', 'easy-hotel')}
                        values={extraServicesMargin}
                        onChange={(nextValues) => setAttributes({ extraServicesMargin: nextValues })}
                    />
                    <Divider />
                    <BoxControl
                        label={__('Checkbox Border Radius', 'easy-hotel')}
                        values={serviceCheckboxBorderRadius}
                        onChange={(nextValues) => setAttributes({ serviceCheckboxBorderRadius: nextValues })}
                    />
                    <BoxControl
                        label={__('Quantity Border Radius', 'easy-hotel')}
                        values={serviceQtyBorderRadius}
                        onChange={(nextValues) => setAttributes({ serviceQtyBorderRadius: nextValues })}
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
                    <BoxControl
                        label={__('Border Radious', 'easy-hotel')}
                        values={submitBtnBorderRadius}
                        onChange={(nextValues) => setAttributes({ submitBtnBorderRadius: nextValues })}
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
