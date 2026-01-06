import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
    GradientPicker,
    Popover,
    Button,
    Icon
} from '@wordpress/components';

const GradientPopover = ({ label, gradient = '', onChange, defaultGradient = '' }) => {
    const [isVisible, setIsVisible] = useState(false);

    const toggleVisible = () => {
        setIsVisible((state) => !state);
    };

    return (
        <div className="eshb-gradient-popover-control" style={{ position: 'relative' }}>
            <Button
                variant="secondary"
                onClick={toggleVisible}
                style={{ width: '100%', justifyContent: 'space-between', marginBottom: '15px', boxShadow: 'none' }}
            >
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', overflow: 'hidden' }}>
                    <div style={{
                        width: '16px',
                        height: '16px',
                        borderRadius: '4px',
                        background: gradient || 'transparent',
                        border: '1px solid #ccc',
                        flexShrink: 0
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
                    <div style={{ padding: '20px', width: '250px' }}>
                        <GradientPicker
                            value={gradient || undefined}
                            onChange={onChange}
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
                            onClick={() => {
                                onChange(defaultGradient);
                                setIsVisible(false);
                            }}
                            style={{ marginTop: '10px', width: '100%', justifyContent: 'center' }}
                        >
                            {__('Reset', 'easy-hotel')}
                        </Button>
                    </div>
                </Popover>
            )}
        </div>
    );
};

export default GradientPopover;
