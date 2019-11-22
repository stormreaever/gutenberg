/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import BlockMover from '../block-mover';
import BlockSwitcher from '../block-switcher';
import BlockControls from '../block-controls';
import BlockFormatControls from '../block-format-controls';
import BlockSettingsMenu from '../block-settings-menu';

export default function BlockToolbar( { moverDirection } ) {
	const { blockClientIds, isValid, mode } = useSelect( ( select ) => {
		const {
			getBlockMode,
			getSelectedBlockClientIds,
			isBlockValid,
		} = select( 'core/block-editor' );
		const selectedBlockClientIds = getSelectedBlockClientIds();

		return {
			blockClientIds: selectedBlockClientIds,
			isValid: selectedBlockClientIds.length === 1 ?
				isBlockValid( selectedBlockClientIds[ 0 ] ) :
				null,
			mode: selectedBlockClientIds.length === 1 ?
				getBlockMode( selectedBlockClientIds[ 0 ] ) :
				null,
		};
	} );

	if ( blockClientIds.length === 0 ) {
		return null;
	}
	const shouldShowVisualToolbar = isValid && mode === 'visual';
	const isMultiToolbar = blockClientIds.length > 1;

	return (
		<div className="editor-block-toolbar block-editor-block-toolbar">
			<BlockMover
				clientIds={ blockClientIds }
				__experimentalOrientation={ moverDirection }
			/>
			{ ( shouldShowVisualToolbar || isMultiToolbar ) && <BlockSwitcher clientIds={ blockClientIds } /> }
			{ shouldShowVisualToolbar && ! isMultiToolbar && (
				<>
					<BlockControls.Slot bubblesVirtually className="block-editor-block-toolbar__slot" />
					<BlockFormatControls.Slot bubblesVirtually className="block-editor-block-toolbar__slot" />
				</>
			) }
			<BlockSettingsMenu clientIds={ blockClientIds } />
		</div>
	);
}
