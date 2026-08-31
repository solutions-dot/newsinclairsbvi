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

	/**
	 * Open a <details> and every <details> it sits inside, closing the
	 * siblings of each as it goes. A deep link can point at a whole
	 * service or at one FAQ within it, and a FAQ is no use revealed
	 * inside a collapsed parent — so the whole ancestor chain opens.
	 */
	function revealTarget(el) {
		var node = el;

		while (node && node !== document.body) {
			if (node.tagName === 'DETAILS') {
				closeSiblingsOf(node);
				node.open = true;
			}
			node = node.parentNode;
		}
	}

	/**
	 * One service open at a time. Scoped to the group the <details>
	 * belongs to, so opening a service does not close an FAQ nested in
	 * it, and vice versa.
	 */
	function closeSiblingsOf(details) {
		var group = details.parentNode;

		if (!group || !group.querySelectorAll) {
			return;
		}

		if (!group.hasAttribute('data-sc-collapses')) {
			return;
		}

		Array.prototype.forEach.call(
			group.querySelectorAll(':scope > details[open]'),
			function (other) {
				if (other !== details) {
					other.open = false;
				}
			}
		);
	}

	function scrollToSection(hash) {
		if (!hash || hash.charAt(0) !== '#') {
			return;
		}

		var target = document.getElementById(hash.slice(1));

		if (!target) {
			return;
		}

		// Expand before measuring: a closed bar has a different height
		// and everything below it would be scrolled to the wrong place.
		revealTarget(target);

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

	/**
	 * An option's value is a bare "#section" when the sections are on this
	 * same page, and an absolute URL when they live on the detail page.
	 * scrollToSection() only understands fragments and returns early on
	 * anything else, so a full URL has to be navigated to instead.
	 */
	function goToValue(value) {
		if (!value) {
			return;
		}

		if (value.charAt(0) === '#') {
			scrollToSection(value);
		} else {
			window.location.href = value;
		}
	}

	function initJump(root) {
		var select = root.querySelector('.sc-jump__select');
		var go = root.querySelector('.sc-jump__go');

		if (!select) {
			return;
		}

		select.addEventListener('change', function () {
			goToValue(select.value);
		});

		if (go) {
			go.addEventListener('click', function () {
				goToValue(select.value);
			});
		}
	}

	/* ------------------------------------------------------------------
	   In-page anchor links (nutshell rows, "All services", nav dropdown)
	   ------------------------------------------------------------------ */

	function initAnchors() {
		document.addEventListener('click', function (event) {
			if (!(event.target instanceof Element)) {
				return;
			}

			var link = event.target.closest('a[href*="#"]');

			if (!link) {
				return;
			}

			// Only ever touch this plugin's own links. This script loads on
			// every page so the nav submenu works, and a document-level
			// handler that swallowed every same-page hash link would break
			// anything else built on one — Elementor tabs, Essential Addons
			// toggles, Royal Addons accordions, modal triggers.
			if (!link.closest('.sc-services') && !link.closest('.sc-nav-dropdown')) {
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
			// Either the injected panel, or — far more usually — the real
			// submenu the theme renders from the seeded menu items. On
			// mobile that submenu starts collapsed, so it needs the same
			// toggle wiring.
			var dropdown = parent.querySelector('.sc-nav-dropdown') ||
				parent.querySelector('.sub-menu');
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

		// This script loads on every page so the nav submenu works, and
		// hashchange fires for every fragment on the site. Without this
		// guard an Elementor tab, a modal or another plugin's accordion
		// sharing an id would get scrolled to and focused by us, on top
		// of whatever it does itself.
		if (!target.closest('.sc-services')) {
			return;
		}

		// Covers both cases: a whole service arrived at from the nav
		// dropdown or the home page, and a single FAQ deep link.
		revealTarget(target);

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

			// .sc-nav-row > a, not .sc-nav-parent > a: the link's direct
			// parent is the row wrapper (see inc/nav.php), so this must
			// follow it there or the click falls through to the theme's
			// own delegated handler, which is exactly what this exists
			// to prevent.
			var link = event.target.closest('.sc-nav-row > a');

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

	/* ------------------------------------------------------------------
	   Collapsed service bars — one open at a time
	   ------------------------------------------------------------------ */

	function initCollapses() {
		Array.prototype.forEach.call(
			document.querySelectorAll('[data-sc-collapse]'),
			function (details) {
				// 'toggle' fires however the element was opened: click,
				// keyboard, or find-in-page auto-expanding it.
				details.addEventListener('toggle', function () {
					if (details.open) {
						closeSiblingsOf(details);
					}
				});
			}
		);
	}

	function init() {
		Array.prototype.forEach.call(
			document.querySelectorAll('[data-sc-jump]'),
			initJump
		);
		initCollapses();
		initAnchors();
		initNavDropdown();
		keepParentClickable();
		openTargetedFaq();
	}

	// Arriving at a new hash on a page already loaded — the back button,
	// or a dropdown link to the page you are on — does not reload, so
	// the deep-link handling has to run again.
	window.addEventListener('hashchange', openTargetedFaq);

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
