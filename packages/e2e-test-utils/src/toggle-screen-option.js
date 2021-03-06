/**
 * Internal dependencies
 */
import { clickOnCloseModalButton } from './click-on-close-modal-button';
import { clickOnMoreMenuItem } from './click-on-more-menu-item';

/**
 * Toggles the screen option with the given label.
 *
 * @param {string}   label           The label of the screen option, e.g. 'Show Tips'.
 * @param {?boolean} shouldBeChecked If true, turns the option on. If false, off. If
 *                                   undefined, the option will be toggled.
 */
export async function toggleScreenOption( label, shouldBeChecked = undefined ) {
	await clickOnMoreMenuItem( 'Preferences' );
	const [ handle ] = await page.$x(
		`//label[contains(text(), "${ label }")]`
	);

	const isChecked = await page.evaluate(
		( element ) => element.control.checked,
		handle
	);
	if ( isChecked !== shouldBeChecked ) {
		await handle.click();
	}

	await clickOnCloseModalButton();
}
