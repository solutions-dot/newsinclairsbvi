/**
 * Front-end interactions: mega-menu, mobile drawer, practice-area hover
 * panel, testimonial rotator, scroll-reveal. Vanilla JS, no dependencies.
 * FAQ accordions need no JS — they're native <details>/<summary>, and the
 * chevron rotation is pure CSS on the [open] attribute.
 */
( function () {
	'use strict';

	/* ------------------------------------------------------------------ */
	/* Header mega-menu                                                    */
	/* ------------------------------------------------------------------ */

	function initMegaMenu() {
		var trigger = document.querySelector( '[data-mega-trigger]' );
		var panel = document.querySelector( '[data-mega-panel]' );
		var caret = trigger ? trigger.querySelector( '.sbvi-nav__caret' ) : null;

		if ( ! trigger || ! panel || ! caret ) {
			return;
		}

		var closeTimer = null;
		var CLOSE_DELAY = 200;

		function open() {
			clearTimeout( closeTimer );
			panel.hidden = false;
			caret.setAttribute( 'aria-expanded', 'true' );
		}

		function close() {
			panel.hidden = true;
			caret.setAttribute( 'aria-expanded', 'false' );
		}

		function scheduleClose() {
			clearTimeout( closeTimer );
			closeTimer = setTimeout( close, CLOSE_DELAY );
		}

		[ trigger, panel ].forEach( function ( el ) {
			el.addEventListener( 'mouseenter', open );
			el.addEventListener( 'mouseleave', scheduleClose );
		} );

		// Click always opens rather than toggling: a mouse click is preceded
		// by a mouseenter on the same element, which has already opened the
		// panel by the time the click fires — a toggle would read that as
		// "already open" and immediately close what the hover just opened.
		// Closing stays available via Escape, an outside click, or moving
		// the mouse away (scheduleClose above).
		caret.addEventListener( 'click', open );

		document.addEventListener( 'click', function ( e ) {
			if ( ! panel.hidden && ! trigger.contains( e.target ) && ! panel.contains( e.target ) ) {
				close();
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && ! panel.hidden ) {
				close();
				caret.focus();
			}
		} );
	}

	/* ------------------------------------------------------------------ */
	/* Mobile drawer                                                       */
	/* ------------------------------------------------------------------ */

	function initMobileDrawer() {
		var hamburger = document.querySelector( '.sbvi-hamburger' );
		var drawer = document.getElementById( 'sbvi-mobile-drawer' );
		var closeBtn = drawer ? drawer.querySelector( '.sbvi-mobile-drawer__close' ) : null;

		if ( ! hamburger || ! drawer || ! closeBtn ) {
			return;
		}

		function open() {
			drawer.hidden = false;
			hamburger.setAttribute( 'aria-expanded', 'true' );
			closeBtn.focus();
			document.body.style.overflow = 'hidden';
		}

		function close() {
			drawer.hidden = true;
			hamburger.setAttribute( 'aria-expanded', 'false' );
			hamburger.focus();
			document.body.style.overflow = '';
		}

		hamburger.addEventListener( 'click', open );
		closeBtn.addEventListener( 'click', close );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && ! drawer.hidden ) {
				close();
			}
		} );
	}

	/* ------------------------------------------------------------------ */
	/* "Choose a practice area" hover / focus panel                        */
	/* ------------------------------------------------------------------ */

	function initPracticePicker() {
		var picker = document.querySelector( '[data-practice-picker]' );
		if ( ! picker ) {
			return;
		}

		var rows = picker.querySelectorAll( '[data-practice-index]' );
		var descs = picker.querySelectorAll( '[data-practice-desc]' );

		function activate( index ) {
			rows.forEach( function ( row ) {
				row.classList.toggle( 'is-active', row.getAttribute( 'data-practice-index' ) === String( index ) );
			} );
			descs.forEach( function ( desc ) {
				desc.classList.toggle( 'is-active', desc.getAttribute( 'data-practice-desc' ) === String( index ) );
			} );
		}

		rows.forEach( function ( row ) {
			var index = row.getAttribute( 'data-practice-index' );
			row.addEventListener( 'mouseenter', function () { activate( index ); } );
			row.addEventListener( 'focus', function () { activate( index ); } );
		} );
	}

	/* ------------------------------------------------------------------ */
	/* Testimonial rotator                                                 */
	/* ------------------------------------------------------------------ */

	function initTestimonials() {
		var stage = document.querySelector( '[data-testimonial-stage]' );
		if ( ! stage ) {
			return;
		}

		var quotes = stage.querySelectorAll( '.sbvi-testimonials__quote' );
		var dotsWrap = document.querySelector( '[data-testimonial-dots]' );
		var dots = dotsWrap ? dotsWrap.querySelectorAll( '.sbvi-testimonials__dot' ) : [];

		if ( quotes.length < 2 ) {
			return;
		}

		var current = 0;
		var timer = null;
		var resizeTimer = null;
		var DURATION = 7000;

		// Real testimonials vary a lot in length — the CSS min-height is
		// just a safe initial guess before this runs. Measure each quote's
		// natural height (briefly taking it out of absolute positioning,
		// since inset:0 would otherwise report the stage's own height back)
		// and size the stage to the tallest one, so a long quote can never
		// overflow into the section below it.
		function resizeStage() {
			var max = 0;
			quotes.forEach( function ( quote ) {
				var prevPosition = quote.style.position;
				var prevVisibility = quote.style.visibility;
				quote.style.position = 'static';
				quote.style.visibility = 'hidden';
				max = Math.max( max, quote.offsetHeight );
				quote.style.position = prevPosition;
				quote.style.visibility = prevVisibility;
			} );
			if ( max > 0 ) {
				stage.style.minHeight = max + 'px';
			}
		}

		function show( index ) {
			current = index;
			quotes.forEach( function ( quote, i ) {
				quote.classList.toggle( 'is-active', i === index );
			} );
			dots.forEach( function ( dot, i ) {
				dot.classList.toggle( 'is-active', i === index );
			} );
		}

		function advance() {
			show( ( current + 1 ) % quotes.length );
		}

		function start() {
			stop();
			timer = setInterval( function () {
				if ( ! document.hidden ) {
					advance();
				}
			}, DURATION );
		}

		function stop() {
			if ( timer ) {
				clearInterval( timer );
			}
		}

		dots.forEach( function ( dot, i ) {
			dot.addEventListener( 'click', function () {
				show( i );
				start();
			} );
		} );

		resizeStage();
		window.addEventListener( 'resize', function () {
			clearTimeout( resizeTimer );
			resizeTimer = setTimeout( resizeStage, 150 );
		} );

		start();
	}

	/* ------------------------------------------------------------------ */
	/* Footer accordion                                                    */
	/* ------------------------------------------------------------------ */

	function initFooterAccordion() {
		var cols = document.querySelectorAll( '.sbvi-footer__col' );
		if ( ! cols.length ) {
			return;
		}

		var MOBILE_BREAKPOINT = 900;
		var resizeTimer = null;

		function sync() {
			var isDesktop = window.innerWidth >= MOBILE_BREAKPOINT;
			cols.forEach( function ( col ) {
				if ( isDesktop ) {
					col.setAttribute( 'open', '' );
				} else {
					col.removeAttribute( 'open' );
				}
			} );
		}

		sync();
		window.addEventListener( 'resize', function () {
			clearTimeout( resizeTimer );
			resizeTimer = setTimeout( sync, 150 );
		} );
	}

	/* ------------------------------------------------------------------ */
	/* Scroll reveal                                                       */
	/* ------------------------------------------------------------------ */

	function initScrollReveal() {
		var targets = document.querySelectorAll( '[data-reveal]' );
		if ( ! targets.length ) {
			return;
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			targets.forEach( function ( el ) { el.classList.add( 'is-visible' ); } );
			return;
		}

		var observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					observer.unobserve( entry.target );
				}
			} );
		}, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 } );

		targets.forEach( function ( el ) { observer.observe( el ); } );
	}

	function init() {
		initMegaMenu();
		initMobileDrawer();
		initPracticePicker();
		initTestimonials();
		initFooterAccordion();
		initScrollReveal();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
