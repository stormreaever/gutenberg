/**
 * External dependencies
 */
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { settings as webSettings } from './index.js';

export { metadata, name } from './index.js';

export const settings = {
	...webSettings,
	__experimentalGetLabel( attributes ) {
		const { content } = attributes;
		return isEmpty( content ) ? __( 'Empty' ) : content;
	},
};
