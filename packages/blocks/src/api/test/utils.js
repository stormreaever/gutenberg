/**
 * External dependencies
 */
import { noop } from 'lodash';

/**
 * Internal dependencies
 */
import { createBlock } from '../factory';
import { getBlockTypes, unregisterBlockType, registerBlockType, setDefaultBlockName } from '../registration';
import { isUnmodifiedDefaultBlock, getBlockLabel } from '../utils';

describe( 'block helpers', () => {
	beforeAll( () => {
		// Initialize the block store
		require( '../../store' );
	} );

	afterEach( () => {
		setDefaultBlockName( undefined );
		getBlockTypes().forEach( ( block ) => {
			unregisterBlockType( block.name );
		} );
	} );

	describe( 'isUnmodifiedDefaultBlock()', () => {
		it( 'should return true if the default block is unmodified', () => {
			registerBlockType( 'core/test-block', {
				attributes: {
					align: {
						type: 'string',
					},
					includesDefault: {
						type: 'boolean',
						default: true,
					},
				},
				save: noop,
				category: 'common',
				title: 'test block',
			} );
			setDefaultBlockName( 'core/test-block' );
			const unmodifiedBlock = createBlock( 'core/test-block' );
			expect( isUnmodifiedDefaultBlock( unmodifiedBlock ) ).toBe( true );
		} );

		it( 'should return false if the default block is updated', () => {
			registerBlockType( 'core/test-block', {
				attributes: {
					align: {
						type: 'string',
					},
					includesDefault: {
						type: 'boolean',
						default: true,
					},
				},
				save: noop,
				category: 'common',
				title: 'test block',
			} );
			setDefaultBlockName( 'core/test-block' );
			const block = createBlock( 'core/test-block' );
			block.attributes.align = 'left';

			expect( isUnmodifiedDefaultBlock( block ) ).toBe( false );
		} );

		it( 'should invalidate cache if the default block name changed', () => {
			registerBlockType( 'core/test-block1', {
				attributes: {
					includesDefault1: {
						type: 'boolean',
						default: true,
					},
				},
				save: noop,
				category: 'common',
				title: 'test block',
			} );
			registerBlockType( 'core/test-block2', {
				attributes: {
					includesDefault2: {
						type: 'boolean',
						default: true,
					},
				},
				save: noop,
				category: 'common',
				title: 'test block',
			} );
			setDefaultBlockName( 'core/test-block1' );
			isUnmodifiedDefaultBlock( createBlock( 'core/test-block1' ) );
			setDefaultBlockName( 'core/test-block2' );
			expect( isUnmodifiedDefaultBlock( createBlock( 'core/test-block2' ) ) ).toBe( true );
		} );
	} );
} );

describe( 'getBlockLabel', () => {
	it( 'returns only the block title when the block has no display name', () => {
		const blockType = { title: 'Recipe' };
		const attributes = {};

		expect( getBlockLabel( blockType, attributes ) ).toBe( 'Recipe' );
	} );

	it( 'returns only the block title when the block has a display name, but the attribute is undefined', () => {
		const blockType = { title: 'Recipe', __experimentalDisplayName: 'heading' };
		const attributes = {};

		expect( getBlockLabel( blockType, attributes ) ).toBe( 'Recipe' );
	} );

	it( 'returns only the block title when the block has a display name, but the attribute is an empty string', () => {
		const blockType = { title: 'Recipe', __experimentalDisplayName: 'heading' };
		const attributes = { heading: '' };

		expect( getBlockLabel( blockType, attributes ) ).toBe( 'Recipe' );
	} );

	it( 'returns the block title with the display name when the display name and its attribute are defined', () => {
		const blockType = { title: 'Recipe', __experimentalDisplayName: 'heading' };
		const attributes = { heading: 'Cupcakes!' };

		expect( getBlockLabel( blockType, attributes ) ).toBe( 'Recipe: Cupcakes!' );
	} );

	it( 'removes any html elements from the display name attribute', () => {
		const blockType = { title: 'Recipe', __experimentalDisplayName: 'heading' };
		const attributes = { heading: '<b><span class="my-class">Cupcakes!</span></b>' };

		expect( getBlockLabel( blockType, attributes ) ).toBe( 'Recipe: Cupcakes!' );
	} );

	it( 'allows specification of a custom separator', () => {
		const blockType = { title: 'Recipe', __experimentalDisplayName: 'heading' };
		const attributes = { heading: 'Cupcakes!' };

		expect( getBlockLabel( blockType, attributes, ' - ' ) ).toBe( 'Recipe - Cupcakes!' );
	} );
} );
