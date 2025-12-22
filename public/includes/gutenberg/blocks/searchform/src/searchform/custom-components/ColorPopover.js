import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
    ColorPicker,
    Popover,
    Button,
    Icon
} from '@wordpress/components';

const ColorPopover = ({ label, color, onChange }) => {
    const [isVisible, setIsVisible] = useState(false);

    const toggleVisible = () => {
        setIsVisible((state) => !state);
    };

    return (
        <div className="eshb-color-popover-control" style={{ position: 'relative' }}>
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
                        backgroundColor: color || 'transparent',
                        border: '1px solid #ccc'
                    }} />
                    {label}
                </div>
                <Icon icon="plus" />
            </Button>
            {isVisible && (
                <Popover
                    position="bottom center"
                    onFocusOutside={() => setIsVisible(false)}
                >
                    <div style={{ padding: '20px' }}>
                        <ColorPicker
                            color={color}
                            onChange={onChange}
                            enableAlpha
                        />
                    </div>
                </Popover>
            )}
        </div>
    );
};

export default ColorPopover;
