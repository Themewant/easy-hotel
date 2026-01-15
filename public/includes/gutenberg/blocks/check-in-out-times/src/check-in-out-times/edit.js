import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';
import './editor.scss';

export default function Edit({ attributes }) {
    return (
        <div {...useBlockProps()}>
            <ServerSideRender
                block={metadata.name}
                attributes={attributes}
            />
        </div>
    );
}
