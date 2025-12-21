import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, ColorPicker, BaseControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
    return (
        <div {...useBlockProps()}>
            <InspectorControls group="styles">
                <PanelBody title={__('Container', 'easy-hotel')}>
                    <BaseControl label={__('Background Color', 'easy-hotel')}>
                        <ColorPicker
                            color={attributes.customBackgroundColor}
                            onChange={(value) => {
                                if (value && typeof value === 'object') {
                                    setAttributes({ customBackgroundColor: value.hex });
                                } else {
                                    setAttributes({ customBackgroundColor: value });
                                }
                            }}
                            enableAlpha
                        />
                    </BaseControl>
                </PanelBody>

                <PanelBody title={__('Fields Group', 'easy-hotel')} initialOpen={false}>
                    {/* Add Fields Group styles here */}
                </PanelBody>

                <PanelBody title={__('Fields', 'easy-hotel')} initialOpen={false}>
                    {/* Add Fields styles here */}
                </PanelBody>

                <PanelBody title={__('Plus Minus Button', 'easy-hotel')} initialOpen={false}>
                    {/* Add Plus Minus Button styles here */}
                </PanelBody>

                <PanelBody title={__('Button', 'easy-hotel')} initialOpen={false}>
                    {/* Add Button styles here */}
                </PanelBody>
            </InspectorControls>
            <ServerSideRender
                block={metadata.name}
            />
        </div>
    );
}
