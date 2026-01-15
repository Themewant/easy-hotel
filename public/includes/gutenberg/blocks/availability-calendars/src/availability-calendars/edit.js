import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';
import './editor.scss';

export default function Edit({ attributes }) {
    return (
        <div {...useBlockProps()}>
            <div className="eshb-block-placeholder" style={{ textAlign: 'center', backgroundColor: '#f1f1f1', padding: '20px' }}>
                <p>{__('Availability Calendars Block will show here at frontend', 'easy-hotel')}</p>
            </div>
        </div>
    );
}
