<?php
/**
 * Service icons.
 *
 * These are the client's existing icons, lifted verbatim from the live
 * /our-services/ markup (the `sc-acc-icon` spans) so the redesign keeps
 * the same visual vocabulary. They are 24×24 Feather-style line icons —
 * no fill, `currentColor` stroke — so they take their colour from CSS.
 *
 * Mapping to the rebuilt sections, where a section was renamed:
 *   aml-cft-compliance     ← "Compliance & Regulatory Law" (shield)
 *   virtual-assets-fintech ← "Digital / Virtual Assets…"   (cube)
 *   banking-finance        ← "Banking & Finance Law"       (bank)
 *   trade-marks            ← "Trademarks"                  (award)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ssvc_icons() {
	$open  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">';
	$close = '</svg>';

	$paths = array(
		// Trending-up chart.
		'investment-business'    => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
		// Two blocks on a baseline.
		'corporate-commercial'   => '<rect x="3" y="3" width="7" height="18"/><rect x="14" y="9" width="7" height="12"/><line x1="3" y1="21" x2="21" y2="21"/>',
		// Shield with a tick.
		'aml-cft-compliance'     => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
		// Isometric cube.
		'virtual-assets-fintech' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
		// Scales of justice.
		'economic-substance'     => '<line x1="12" y1="3" x2="12" y2="21"/><line x1="3" y1="9" x2="21" y2="9"/><path d="M5 9l2 9a2 2 0 0 0 4 0L5 9"/><path d="M19 9l-2 9a2 2 0 0 1-4 0L19 9"/><line x1="7" y1="21" x2="17" y2="21"/>',
		// Classical bank facade.
		'banking-finance'        => '<line x1="3" y1="22" x2="21" y2="22"/><line x1="6" y1="18" x2="6" y2="11"/><line x1="10" y1="18" x2="10" y2="11"/><line x1="14" y1="18" x2="14" y2="11"/><line x1="18" y1="18" x2="18" y2="11"/><polygon points="12 2 20 7 4 7"/>',
		// Location pin.
		'trusts-estates'         => '<path d="M12 2a7 7 0 0 1 7 7c0 5-7 13-7 13S5 14 5 9a7 7 0 0 1 7-7z"/><circle cx="12" cy="9" r="2.5"/>',
		// Funnel.
		'voluntary-liquidations' => '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',
		// Award rosette.
		'trade-marks'            => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/>',
		// Notary stamp.
		'notarial-services'      => '<path d="M5 22h14"/><rect x="4" y="14" width="16" height="4" rx="1"/><path d="M8 14v-2a4 4 0 0 1 8 0v2"/><circle cx="12" cy="8" r="3"/>',
	);

	$icons = array();
	foreach ( $paths as $id => $d ) {
		$icons[ $id ] = $open . $d . $close;
	}

	return apply_filters( 'sinclairs_services_icons', $icons );
}

/**
 * Inline SVG for a section id, or '' when there is no icon for it.
 */
function ssvc_icon( $section_id ) {
	$icons = ssvc_icons();
	return isset( $icons[ $section_id ] ) ? $icons[ $section_id ] : '';
}
