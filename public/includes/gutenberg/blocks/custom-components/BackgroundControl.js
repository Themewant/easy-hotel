import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
    ColorPicker,
    GradientPicker,
    Popover,
    Button,
    Icon,
    TabPanel
} from '@wordpress/components';

const BackgroundControl = ({
    label,
    colorValue,
    gradientValue,
    onColorChange,
    onGradientChange,
    defaultColor = '',
    defaultGradient = ''
}) => {
    const [isVisible, setIsVisible] = useState(false);

    const toggleVisible = () => {
        setIsVisible((state) => !state);
    };

    // Determine what to show in the preview
    // If gradient is present, show it. Otherwise show color.
    const previewStyle = {};
    if (gradientValue) {
        previewStyle.background = gradientValue;
    } else if (colorValue) {
        previewStyle.backgroundColor = colorValue;
    } else {
        previewStyle.background = 'transparent';
    }

    return (
        <div className="eshb-background-control" style={{ position: 'relative', marginBottom: '15px' }}>
            <Button
                variant="secondary"
                onClick={toggleVisible}
                style={{ width: '100%', justifyContent: 'space-between', boxShadow: 'none' }}
            >
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', overflow: 'hidden' }}>
                    <div style={{
                        width: '16px',
                        height: '16px',
                        borderRadius: '4px',
                        border: '1px solid #ccc',
                        flexShrink: 0,
                        ...previewStyle
                    }} />
                    <span style={{
                        whiteSpace: 'nowrap',
                        overflow: 'hidden',
                        textOverflow: 'ellipsis',
                        display: 'block'
                    }}>
                        {label}
                    </span>
                </div>
                <Icon icon="plus" />
            </Button>
            {isVisible && (
                <Popover
                    position="bottom center"
                    onFocusOutside={() => setIsVisible(false)}
                >
                    <div style={{ padding: '0', width: '280px' }}>
                        <TabPanel
                            className="eshb-background-tabs"
                            activeClass="is-active"
                            tabs={[
                                { name: 'solid', title: __('Solid', 'easy-hotel'), className: 'eshb-bg-tab-solid' },
                                { name: 'gradient', title: __('Gradient', 'easy-hotel'), className: 'eshb-bg-tab-gradient' },
                            ]}
                        >
                            {(tab) => {
                                if (tab.name === 'solid') {
                                    return (
                                        <div style={{ padding: '16px' }}>
                                            <ColorPicker
                                                color={colorValue}
                                                onChange={onColorChange}
                                                enableAlpha
                                            />
                                            <Button
                                                variant="secondary"
                                                isSmall
                                                onClick={() => onColorChange(defaultColor)}
                                                style={{ marginTop: '10px', width: '100%', justifyContent: 'center' }}
                                            >
                                                {__('Reset Color', 'easy-hotel')}
                                            </Button>
                                        </div>
                                    );
                                } else if (tab.name === 'gradient') {
                                    return (
                                        <div style={{ padding: '16px' }}>
                                            <GradientPicker
                                                value={gradientValue || undefined}
                                                onChange={onGradientChange}
                                                gradients={[
                                                    {
                                                        name: 'Primary',
                                                        gradient: 'linear-gradient(180deg, rgba(171, 137, 101, 0) 0%, var(--eshb-primary-color) 100%)',
                                                        slug: 'primary',
                                                    },
                                                    {
                                                        name: 'Warm Morning',
                                                        gradient: 'linear-gradient(135deg, #ff9a9e 0%, #fad0c4 99%, #fad0c4 100%)',
                                                        slug: 'warm-morning',
                                                    },
                                                    {
                                                        name: 'Sunny Days',
                                                        gradient: 'linear-gradient(135deg, #f6d365 0%, #fda085 100%)',
                                                        slug: 'sunny-days',
                                                    },
                                                    {
                                                        name: 'Deep Blue',
                                                        gradient: 'linear-gradient(135deg, #2b5876 0%, #4e4376 100%)',
                                                        slug: 'deep-blue',
                                                    },
                                                    {
                                                        name: 'Premium Dark',
                                                        gradient: 'linear-gradient(135deg, #232526 0%, #414345 100%)',
                                                        slug: 'premium-dark',
                                                    }
                                                ]}
                                            />
                                            <Button
                                                variant="secondary"
                                                isSmall
                                                onClick={() => onGradientChange(defaultGradient)}
                                                style={{ marginTop: '10px', width: '100%', justifyContent: 'center' }}
                                            >
                                                {__('Reset Gradient', 'easy-hotel')}
                                            </Button>
                                        </div>
                                    );
                                }
                            }}
                        </TabPanel>
                    </div>
                </Popover>
            )}
        </div>
    );
};

export default BackgroundControl;
