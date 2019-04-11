/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';
import { Fragment } from '@wordpress/element';

/**
 * Internal dependencies
 */
import BlockSwitcher from '../block-switcher';
import BlockControls from '../block-controls';
import MultiBlockControls from '../block-controls/multi-block-controls';
import MultiBlocksSwitcher from '../block-switcher/multi-blocks-switcher';
import BlockFormatControls from '../block-format-controls';
import BlockSettingsMenu from '../block-settings-menu';

function BlockToolbar( { blockClientIds, isValid, mode } ) {
	if ( blockClientIds.length === 0 ) {
		return null;
	}

	if ( blockClientIds.length > 1 ) {
		return (
			<div className="editor-block-toolbar block-editor-block-toolbar">
				<MultiBlocksSwitcher />
				<MultiBlockControls.Slot />
				<BlockSettingsMenu clientIds={ blockClientIds } />
			</div>
		);
	}

	return (
		<div className="editor-block-toolbar block-editor-block-toolbar">
			{ mode === 'visual' && isValid && (
				<Fragment>
					<BlockSwitcher clientIds={ blockClientIds } />
					<MultiBlockControls.Slot />
					<BlockControls.Slot />
					<BlockFormatControls.Slot />
				</Fragment>
			) }
			<BlockSettingsMenu clientIds={ blockClientIds } />
		</div>
	);
}

export default withSelect( ( select ) => {
	const {
		getBlockMode,
		getSelectedBlockClientIds,
		isBlockValid,
	} = select( 'core/block-editor' );
	const blockClientIds = getSelectedBlockClientIds();

	return {
		blockClientIds,
		isValid: blockClientIds.length === 1 ? isBlockValid( blockClientIds[ 0 ] ) : null,
		mode: blockClientIds.length === 1 ? getBlockMode( blockClientIds[ 0 ] ) : null,
	};
} )( BlockToolbar );
