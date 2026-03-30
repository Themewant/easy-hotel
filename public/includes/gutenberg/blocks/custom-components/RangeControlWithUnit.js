import { __ } from '@wordpress/i18n';
import {
    RangeControl,
    SelectControl,
} from '@wordpress/components';

const RangeControlWithUnit = ({
    attributes,
    setAttributes,
    attributeKey,
    label = __('Size', 'boldpost'),
    units = ['px', '%', 'em', 'rem', 'vw', 'vh'],
    min,
    max,
    step,
}) => {

    const currentValue = attributes[attributeKey];

    // Parse the value
    const parseValue = (val) => {
        if (val === undefined || val === null || val === '') return { value: '', unit: 'px' };
        const match = String(val).match(/^([\d.-]+)([a-z%]*)$/);
        return {
            value: match ? parseFloat(match[1]) : 0,
            unit: (match && match[2]) ? match[2] : 'px'
        };
    };

    const { value, unit } = parseValue(currentValue);

    const onChangeValue = (newValue) => {
        // If the value is cleared (undefined), don't set a unit-only string
        if (newValue === undefined) {
            setAttributes({ [attributeKey]: '' });
            return;
        }
        setAttributes({ [attributeKey]: `${newValue}${unit}` });
    };

    const onChangeUnit = (newUnit) => {
        setAttributes({ [attributeKey]: `${value || 0}${newUnit}` });
    };

    // Determine min/max/step based on unit if not provided
    const getSettings = (currentUnit) => {
        if (currentUnit === 'px') return { min: min !== undefined ? min : 0, max: max !== undefined ? max : 100, step: step || 1 };
        if (currentUnit === '%') return { min: min !== undefined ? min : 0, max: max !== undefined ? max : 100, step: step || 1 };
        if (['em', 'rem', 'vw', 'vh'].includes(currentUnit)) return { min: min !== undefined ? min : 0, max: max !== undefined ? max : 10, step: step || 0.1 };
        return { min: 0, max: 100, step: 1 };
    };

    const currentSettings = getSettings(unit);

    return (
        <div className="boldpost-range-control-with-unit" style={{ marginBottom: '24px' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' }}>
                <span className="components-base-control__label" style={{ marginBottom: 0, marginRight: '10px' }}>{label}</span>
                <div style={{ width: '80px' }}>
                    <SelectControl
                        value={unit}
                        options={units.map((u) => ({ label: u, value: u }))}
                        onChange={onChangeUnit}
                        __nextHasNoMarginBottom={true}
                        __next40pxDefaultSize
                        size="__unstable-small"
                    />
                </div>
            </div>
            <RangeControl
                value={value}
                onChange={onChangeValue}
                min={currentSettings.min}
                max={currentSettings.max}
                step={currentSettings.step}
                withInputField={true}
                allowReset={true}
                __nextHasNoMarginBottom={true}
            />
        </div>
    );
};

export default RangeControlWithUnit;