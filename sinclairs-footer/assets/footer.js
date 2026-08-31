/**
 * Sinclairs (BVI) — Footer.
 *
 * The columns are <details> elements that arrive open. All this does is
 * close them on a narrow screen and re-open them on a wide one, so the
 * footer is an accordion on a phone and three plain columns on desktop.
 *
 * Nothing here is required for the footer to work: with the script
 * absent every column stays open, which is the correct fallback.
 */
(function () {
	'use strict';

	var BREAKPOINT = '(max-width: 600px)';

	function apply(query) {
		var folds = document.querySelectorAll('.sbvif--collapsible .sbvif__fold');

		Array.prototype.forEach.call(folds, function (fold, i) {
			if (!query.matches) {
				fold.open = true;
				return;
			}

			// Someone who has opened a section on their phone should not
			// have it shut again by a resize, an on-screen keyboard
			// appearing, or the address bar collapsing — all of which
			// fire this. Only the first pass sets the closed state.
			if (fold.dataset.sbvifTouched === 'yes') {
				return;
			}

			// The first column stays open so the footer does not
			// collapse to three bare words with nothing under them.
			fold.open = ( i === 0 );
		});
	}

	function init() {
		var query = window.matchMedia(BREAKPOINT);

		apply(query);

		Array.prototype.forEach.call(
			document.querySelectorAll('.sbvif--collapsible .sbvif__fold'),
			function (fold) {
				fold.addEventListener('toggle', function () {
					if (window.matchMedia(BREAKPOINT).matches) {
						fold.dataset.sbvifTouched = 'yes';
					}
				});
			}
		);

		if (query.addEventListener) {
			query.addEventListener('change', function () { apply(query); });
		} else if (query.addListener) {
			// Safari before 14.
			query.addListener(function () { apply(query); });
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
