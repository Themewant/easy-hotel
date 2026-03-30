import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';
import { Button, Tooltip } from '@wordpress/components';

const ResponsiveWrapper = ({ children, label }) => {

    // Get current device from global store
    const deviceType = useSelect((select) => {
        const store = select('core/edit-post');
        return store ? store.__experimentalGetPreviewDeviceType() : 'Desktop';
    }, []);

    const device = deviceType ? deviceType.toLowerCase() : 'desktop';

    // Get dispatcher safely
    const dispatch = useDispatch('core/edit-post');
    const setPreviewDeviceType = dispatch ? dispatch.__experimentalSetPreviewDeviceType : null;

    const setDeviceAndPreview = (deviceName) => {
        if (setPreviewDeviceType) {
            const wpDevice = deviceName.charAt(0).toUpperCase() + deviceName.slice(1);
            setPreviewDeviceType(wpDevice);
        }
    };

    const devices = [
        { name: 'desktop', icon: 'desktop', label: __('Desktop', 'boldpost') },
        { name: 'tablet', icon: 'tablet', label: __('Tablet', 'boldpost') },
        { name: 'mobile', icon: 'smartphone', label: __('Mobile', 'boldpost') },
    ];

    return (
        <div className="eshb-responsive-wrapper" style={{ marginBottom: '20px' }}>
            <div className="eshb-responsive-header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' }}>
                {label && <label className="components-base-control__label">{label}</label>}
                <div className="eshb-responsive-icons">
                    {devices.map((d) => (
                        <Tooltip key={d.name} text={d.label}>
                            <Button
                                isSmall
                                variant={device === d.name ? 'primary' : 'tertiary'}
                                icon={d.icon}
                                onClick={() => setDeviceAndPreview(d.name)}
                                style={{ marginLeft: '5px' }}
                            />
                        </Tooltip>
                    ))}
                </div>
            </div>
            <div className="eshb-responsive-content">
                {children(device)}
            </div>
        </div>
    );
};

export default ResponsiveWrapper;
