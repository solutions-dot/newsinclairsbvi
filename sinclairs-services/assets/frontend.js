/**
 * Sinclairs (BVI) — Our Services
 *
 * Progressive enhancement only. With JavaScript off, the nutshell index
 * still links to every section, the FAQ <details> still open natively,
 * and the nav dropdown still opens on hover via CSS.
 */
(function () {
	'use strict';

	/**
	 * Anchors sit under a sticky header, so scroll-margin-top is set in
	 * CSS. Reading it here keeps JS and CSS from disagreeing about the
	 * offset if the header height is ever retuned.
	 */
	function anchorOffset(el) {
		var declared = window.getComputedStyle(el).scrollMarginTop;
		var parsed = parseInt(declared, 10);
		return isNaN(parsed) ? 0 : parsed;
	}

	function scrollToSection(hash) {
		if (!hash || hash.charAt(0) !== '#') {
			return;
		}

		var target = document.getElementById(hash.slice(1));

		if (!target) {
			return;
		}

		var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var top = target.getBoundingClientRect().top + window.pageYOffset - anchorOffset(target);

		window.scrollTo({
			top: top,
			behavior: reduce ? 'auto' : 'smooth'
		});

		// Keep the URL shareable, without the jump a plain hash change causes.
		if (window.history && window.history.replaceState) {
			window.history.replaceState(null, '', hash);
		}

		// Move keyboard focus so the section is where tabbing resumes.
		var hadTabindex = target.hasAttribute('tabindex');
		if (!hadTabindex) {
			target.setAttribute('tabindex', '-1');
		}
		target.focus({ preventScroll: true });
		if (!hadTabindex) {
			target.addEventListener('blur', function handler() {
				target.removeAttribute('tabindex');
				target.removeEventListener('blur', handler);
			});
		}
	}

	/* ------------------------------------------------------------------
	   Jump-to dropdown
	   ------------------------------------------------------------------ */

	function initJump(root) {
		var select = root.querySelector('.sc-jump__select');
		var go = root.querySelector('.sc-jump__go');

		if (!select) {
			return;
		}

		select.addEventListener('change', function () {
			scrollToSection(select.value);
		});

		if (go) {
			go.addEventListener('click', function () {
				scrollToSection(select.value);
			});
		}
	}

	/* ------------------------------------------------------------------
	   In-page anchor links (nutshell rows, "All services", nav dropdown)
	   ------------------------------------------------------------------ */

	function initAnchors() {
		document.addEventListener('click', function (event) {
			var link = event.target.closest('a[href*="#"]');

			if (!link) {
				return;
			}

			// Only handle links pointing at this same page.
			if (link.pathname !== window.location.pathname ||
				link.host !== window.location.host) {
				return;
			}

			var hash = link.hash;

			if (!hash || !document.getElementById(hash.slice(1))) {
				return;
			}

			event.preventDefault();
			scrollToSection(hash);

			// Close the nav dropdown if the click came from inside one.
			var open = link.closest('.sc-nav-dropdown');
			if (open) {
				open.classList.remove('is-open');
			}
		});
	}

	/* ------------------------------------------------------------------
	   Nav dropdown — CSS handles hover and focus-within; this adds
	   click/tap toggling for touch devices, where hover doesn't exist.
	   ------------------------------------------------------------------ */

	function initNavDropdown() {
		var parents = document.querySelectorAll('.sc-nav-parent');

		Array.prototype.forEach.call(parents, function (parent) {
			var dropdown = parent.querySelector('.sc-nav-dropdown');
			var toggle = parent.querySelector('.sc-nav-toggle');

			if (!dropdown || !toggle) {
				return;
			}

			toggle.addEventListener('click', function (event) {
				event.preventDefault();
				var isOpen = dropdown.classList.toggle('is-open');
				toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});

			document.addEventListener('click', function (event) {
				if (!parent.contains(event.target)) {
					dropdown.classList.remove('is-open');
					toggle.setAttribute('aria-expanded', 'false');
				}
			});

			parent.addEventListener('keydown', function (event) {
				if (event.key === 'Escape') {
					dropdown.classList.remove('is-open');
					toggle.setAttribute('aria-expanded', 'false');
					toggle.focus();
				}
			});
		});
	}

	/* ------------------------------------------------------------------
	   Deep links: /our-services/#trade-marks-faq-2 should land on the
	   section with that FAQ already open.
	   ------------------------------------------------------------------ */

	function openTargetedFaq() {
		if (!window.location.hash) {
			return;
		}

		var target = document.getElementById(window.location.hash.slice(1));

		if (!target) {
			return;
		}

		if (target.tagName === 'DETAILS') {
			target.open = true;
		}

		// Re-run the offset scroll: the browser's own hash jump happens
		// before our CSS scroll-margin is necessarily applied.
		window.setTimeout(function () {
			scrollToSection(window.location.hash);
		}, 50);
	}

	/* ------------------------------------------------------------------
	   Keep "Our Services" clickable
	   The theme cancels navigation on every top-level menu link that has
	   children:

	     $(document).on('click',
	       '.navbar-area .navbar-nav li.menu-item-has-children>a',
	       function (e) { e.preventDefault(); ... });

	   Seeding the ten sub-items gave "Our Services" children for the
	   first time, which silently turned it into a toggle-only link. This
	   listener runs in the capture phase on document — before that
	   delegated bubble handler can see the event — stops it propagating,
	   and performs the navigation itself.

	   Safe for the submenu: on desktop it opens on hover, and on mobile
	   the CSS renders it open inline, so the parent never needs to act
	   as a toggle.
	   ------------------------------------------------------------------ */

	function keepParentClickable() {
		document.addEventListener('click', function (event) {
			if (!(event.target instanceof Element)) {
				return;
			}

			var link = event.target.closest('.sc-nav-parent > a');

			if (!link) {
				return;
			}

			var href = link.getAttribute('href');

			// A placeholder link genuinely is just a toggle — leave it be.
			if (!href || href === '#') {
				return;
			}

			// Modified clicks (new tab, download, middle-click) must keep
			// their native behaviour.
			if (event.metaKey || event.ctrlKey || event.shiftKey ||
				event.altKey || event.button !== 0) {
				return;
			}

			event.stopPropagation();
			window.location.href = link.href;
		}, true);
	}

	function init() {
		Array.prototype.forEach.call(
			document.querySelectorAll('[data-sc-jump]'),
			initJump
		);
		initAnchors();
		initNavDropdown();
		keepParentClickable();
		openTargetedFaq();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
