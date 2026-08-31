<?php
/**
 * Inline SVG icons.
 *
 * Inline rather than an icon font: no extra request, no flash of
 * missing glyphs, and each one inherits currentColor so it takes the
 * footer's text colour without being styled separately. All are marked
 * aria-hidden — the row's own text is what a screen reader should read,
 * and the visually hidden label beside it says what kind of thing it is.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sbvif_icon( $name ) {
	$open  = '<svg class="sbvif__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">';
	$close = '</svg>';

	$paths = array(
		'phone'  => '<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.24 11.4 11.4 0 0 0 3.6.58 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11.4 11.4 0 0 0 .58 3.6 1 1 0 0 1-.25 1z"/>',
		'mail'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6.5 8.5 6 8.5-6"/>',
		'pin'    => '<path d="M20 10c0 5.2-8 12-8 12s-8-6.8-8-12a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="2.8"/>',
		'clock'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5.2l3.4 2"/>',
		'arrow'  => '<path d="M4 12h15"/><path d="m13 6 6 6-6 6"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return $open . $paths[ $name ] . $close;
}
