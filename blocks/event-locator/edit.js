import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<strong>{ __( 'Locations Map', 'locations-map' ) }</strong>
			<p>{ __( 'The interactive map will appear on the frontend.', 'locations-map' ) }</p>
		</div>
	);
}
