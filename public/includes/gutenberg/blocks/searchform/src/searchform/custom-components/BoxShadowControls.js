import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
    RangeControl,
    ColorPicker,
    BaseControl,
    Popover,
    Button,
    Icon
} from '@wordpress/components';

const BoxShadowControls = ({ attributes, setAttributes, state = 'normal' }) => {
    const isHover = state === 'hover';
    const xKey = isHover ? 'boxShadowXHover' : 'boxShadowX';
    const yKey = isHover ? 'boxShadowYHover' : 'boxShadowY';
    const bKey = isHover ? 'boxShadowBlurHover' : 'boxShadowBlur';
    const sKey = isHover ? 'boxShadowSpreadHover' : 'boxShadowSpread';
    const cKey = isHover ? 'boxShadowColorHover' : 'boxShadowColor';
    const mainKey = isHover ? 'boxShadowHover' : 'boxShadow';

    const boxShadowX = attributes[xKey];
    const boxShadowY = attributes[yKey];
    const boxShadowBlur = attributes[bKey];
    const boxShadowSpread = attributes[sKey];
    const boxShadowColor = attributes[cKey];

    const [isVisible, setIsVisible] = useState(false);

    const toggleVisible = () => {
        setIsVisible((state) => !state);
    };

    const updateShadow = (newAttrs) => {
        const nextAttributes = {
            [xKey]: attributes[xKey],
            [yKey]: attributes[yKey],
            [bKey]: attributes[bKey],
            [sKey]: attributes[sKey],
            [cKey]: attributes[cKey],
            ...newAttrs
        };

        const x = nextAttributes[xKey];
        const y = nextAttributes[yKey];
        const b = nextAttributes[bKey];
        const s = nextAttributes[sKey];
        const c = nextAttributes[cKey];

        const shadowString = `${x}px ${y}px ${b}px ${s}px ${c || 'rgba(0,0,0,0.1)'}`;

        setAttributes({
            ...newAttrs,
            [mainKey]: shadowString
        });
    };

    return (
        <div className="eshb-box-shadow-control" style={{ position: 'relative' }}>
            <Button
                variant="secondary"
                onClick={toggleVisible}
                style={{ width: '100%', justifyContent: 'space-between', marginBottom: '15px', boxShadow: 'none' }}
            >
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <div style={{
                        width: '16px',
                        height: '16px',
                        borderRadius: '4px',
                        boxShadow: attributes[mainKey] || 'none',
                        border: '1px solid #ccc',
                        backgroundColor: '#fff'
                    }} />
                    {isHover ? __('Box Shadow (Hover)', 'easy-hotel') : __('Box Shadow Options', 'easy-hotel')}
                </div>
                <Icon icon="plus" />
            </Button>
            {isVisible && (
                <Popover
                    position="bottom center"
                    onFocusOutside={() => setIsVisible(false)}
                >
                    <div style={{ padding: '20px', width: '260px' }}>
                        <RangeControl
                            label={__('Horizontal Offset', 'easy-hotel')}
                            value={boxShadowX}
                            onChange={(val) => updateShadow({ [xKey]: val })}
                            min={-50} max={50}
                        />
                        <RangeControl
                            label={__('Vertical Offset', 'easy-hotel')}
                            value={boxShadowY}
                            onChange={(val) => updateShadow({ [yKey]: val })}
                            min={-50} max={50}
                        />
                        <RangeControl
                            label={__('Blur', 'easy-hotel')}
                            value={boxShadowBlur}
                            onChange={(val) => updateShadow({ [bKey]: val })}
                            min={0} max={100}
                        />
                        <RangeControl
                            label={__('Spread', 'easy-hotel')}
                            value={boxShadowSpread}
                            onChange={(val) => updateShadow({ [sKey]: val })}
                            min={-50} max={50}
                        />
                        <BaseControl label={__('Shadow Color', 'easy-hotel')}>
                            <ColorPicker
                                color={boxShadowColor}
                                onChange={(color) => {
                                    const hex = (color && typeof color === 'object') ? color.hex : color;
                                    updateShadow({ [cKey]: hex });
                                }}
                                enableAlpha
                            />
                        </BaseControl>
                    </div>
                </Popover>
            )}
        </div>
    );
};

export default BoxShadowControls;