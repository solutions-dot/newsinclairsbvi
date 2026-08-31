/**
 * Sinclairs (BVI) — Testimonials carousel.
 *
 * No dependencies. Progressive enhancement: the markup is every
 * testimonial as ordinary text, and without this script they stack down
 * the page and stay readable. The script adds the carousel — one at a
 * time, a height that follows the content, and the controls.
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia &&
		window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function Carousel(root) {
		var config = {};

		try {
			config = JSON.parse(root.getAttribute('data-sbvit') || '{}');
		} catch (e) {
			config = {};
		}

		this.root = root;
		this.config = config;
		this.viewport = root.querySelector('[data-sbvit-viewport]');
		this.items = Array.prototype.slice.call(root.querySelectorAll('[data-sbvit-item]'));
		this.dots = Array.prototype.slice.call(root.querySelectorAll('[data-sbvit-goto]'));
		this.index = 0;
		this.timer = null;
		this.paused = {};
		// Autoplay is off entirely under a reduced-motion preference:
		// text that moves on by itself is motion too, and a testimonial
		// is something people are mid-way through reading.
		this.playing = !!config.autoplay && !reduceMotion && this.items.length > 1;

		if (!this.viewport || !this.items.length) {
			return;
		}

		// Only now do the slides start overlapping. Before this class is
		// set they are static, which is what keeps the no-JS rendering
		// sane rather than a pile of absolutely positioned text.
		root.classList.add('is-live');

		this.setHeight();
		this.watchResize();

		if (this.items.length > 1) {
			this.bind();
		}

		if (this.playing) {
			this.play();
		}
	}

	/**
	 * Pin the viewport to the active testimonial's height, so the card
	 * fits its content instead of every testimonial sitting at the
	 * height of the longest one.
	 */
	Carousel.prototype.setHeight = function () {
		var active = this.items[this.index];

		if (!active) {
			return;
		}

		var height = active.getBoundingClientRect().height;

		if (height) {
			this.viewport.style.height = height + 'px';
		}
	};

	/**
	 * Re-measure when the width changes, because a narrower card wraps
	 * the text into more lines and the height set for the old width
	 * would clip it. Fonts loading late move it too, hence observing the
	 * slide itself rather than listening for window resize alone.
	 */
	Carousel.prototype.watchResize = function () {
		var self = this;

		if ('ResizeObserver' in window) {
			var observer = new ResizeObserver(function () {
				self.setHeight();
			});

			this.items.forEach(function (item) {
				observer.observe(item);
			});

			return;
		}

		var timer = null;

		window.addEventListener('resize', function () {
			window.clearTimeout(timer);
			timer = window.setTimeout(function () {
				self.setHeight();
			}, 120);
		});
	};

	Carousel.prototype.bind = function () {
		var self = this;

		this.dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				self.goTo(parseInt(dot.getAttribute('data-sbvit-goto'), 10));
			});
		});

		var prev = this.root.querySelector('[data-sbvit-prev]');
		var next = this.root.querySelector('[data-sbvit-next]');

		if (prev) {
			prev.addEventListener('click', function () { self.step(-1); });
		}
		if (next) {
			next.addEventListener('click', function () { self.step(1); });
		}

		this.root.addEventListener('keydown', function (event) {
			if (event.key === 'ArrowLeft') {
				self.step(-1);
			} else if (event.key === 'ArrowRight') {
				self.step(1);
			}
		});

		// Pause reasons are tracked separately so that clearing one does
		// not restart autoplay while another still holds — moving the
		// pointer away should not resume under someone still tabbing
		// through it.
		this.root.addEventListener('mouseenter', function () { self.pause('hover'); });
		this.root.addEventListener('mouseleave', function () { self.resume('hover'); });
		this.root.addEventListener('focusin', function () { self.pause('focus'); });
		this.root.addEventListener('focusout', function () { self.resume('focus'); });

		document.addEventListener('visibilitychange', function () {
			if (document.hidden) {
				self.pause('hidden');
			} else {
				self.resume('hidden');
			}
		});

		if ('IntersectionObserver' in window) {
			this.pause('offscreen');

			new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						self.resume('offscreen');
					} else {
						self.pause('offscreen');
					}
				});
			}, { threshold: 0.2 }).observe(this.root);
		}

		this.bindSwipe();
	};

	Carousel.prototype.bindSwipe = function () {
		var self = this;
		var startX = 0;
		var startY = 0;
		var tracking = false;

		this.viewport.addEventListener('touchstart', function (event) {
			var touch = event.changedTouches[0];
			startX = touch.clientX;
			startY = touch.clientY;
			tracking = true;
		}, { passive: true });

		this.viewport.addEventListener('touchend', function (event) {
			if (!tracking) {
				return;
			}
			tracking = false;

			var touch = event.changedTouches[0];
			var dx = touch.clientX - startX;
			var dy = touch.clientY - startY;

			// Horizontal and decisive, or it is a page scroll and none
			// of our business.
			if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy) * 1.5) {
				self.step(dx < 0 ? 1 : -1);
			}
		}, { passive: true });
	};

	Carousel.prototype.step = function (direction) {
		var last = this.items.length - 1;
		var next = this.index + direction;

		if (next > last) {
			next = 0;
		}
		if (next < 0) {
			next = last;
		}

		this.goTo(next);
	};

	Carousel.prototype.goTo = function (index) {
		if (isNaN(index) || index === this.index || !this.items[index]) {
			return;
		}

		this.items[this.index].classList.remove('is-active');
		this.index = index;
		this.items[index].classList.add('is-active');

		this.dots.forEach(function (dot, i) {
			dot.classList.toggle('is-active', i === index);
			dot.setAttribute('aria-selected', i === index ? 'true' : 'false');
		});

		this.setHeight();

		// The visitor is driving; give the one they chose its full time.
		if (this.playing) {
			this.play();
		}
	};

	Carousel.prototype.play = function () {
		var self = this;

		this.stop();

		this.timer = window.setTimeout(function () {
			self.step(1);
		}, this.config.delay || 9000);
	};

	Carousel.prototype.stop = function () {
		if (this.timer) {
			window.clearTimeout(this.timer);
			this.timer = null;
		}
	};

	Carousel.prototype.pause = function (reason) {
		this.paused[reason || 'manual'] = true;
		this.stop();
	};

	Carousel.prototype.resume = function (reason) {
		delete this.paused[reason || 'manual'];

		if (!this.playing || this.timer) {
			return;
		}

		for (var key in this.paused) {
			if (Object.prototype.hasOwnProperty.call(this.paused, key)) {
				return;
			}
		}

		this.play();
	};

	function init() {
		Array.prototype.forEach.call(document.querySelectorAll('.sbvit'), function (root) {
			if (!root.sbvitInstance) {
				root.sbvitInstance = new Carousel(root);
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	// Web fonts land after DOMContentLoaded and change the wrap, so the
	// height measured a moment ago is now wrong.
	if (document.fonts && document.fonts.ready) {
		document.fonts.ready.then(function () {
			Array.prototype.forEach.call(document.querySelectorAll('.sbvit'), function (root) {
				if (root.sbvitInstance && root.sbvitInstance.setHeight) {
					root.sbvitInstance.setHeight();
				}
			});
		});
	}
})();
