/*
 * Sinclairs (BVI) Hero Slider.
 *
 * No dependencies. Every instance on the page runs independently, so a
 * page can carry more than one slider without them fighting over the
 * autoplay timer.
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function Slider(root) {
		var config = {};

		try {
			config = JSON.parse(root.getAttribute('data-sbvis') || '{}');
		} catch (e) {
			config = {};
		}

		this.root = root;
		this.config = config;
		this.slides = Array.prototype.slice.call(root.querySelectorAll('[data-sbvis-slide]'));
		this.dots = Array.prototype.slice.call(root.querySelectorAll('[data-sbvis-goto]'));
		this.index = 0;
		this.timer = null;
		this.rafId = null;
		this.startedAt = 0;
		// Reasons autoplay is currently held; see pause()/resume().
		this.paused = {};
		// Autoplay is off entirely for visitors who asked for reduced
		// motion — a slide that changes under you is motion too.
		this.playing = !!config.autoplay && !reduceMotion && this.slides.length > 1;

		if (this.slides.length < 2) {
			return;
		}

		this.bind();

		if (this.playing) {
			this.play();
		}
	}

	Slider.prototype.bind = function () {
		var self = this;

		this.dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				self.goTo(parseInt(dot.getAttribute('data-sbvis-goto'), 10), true);
			});
		});

		var prev = this.root.querySelector('[data-sbvis-prev]');
		var next = this.root.querySelector('[data-sbvis-next]');

		if (prev) {
			prev.addEventListener('click', function () { self.step(-1, true); });
		}
		if (next) {
			next.addEventListener('click', function () { self.step(1, true); });
		}

		this.root.addEventListener('keydown', function (event) {
			if (event.key === 'ArrowLeft') {
				self.step(-1, true);
			} else if (event.key === 'ArrowRight') {
				self.step(1, true);
			}
		});

		if (this.config.pauseOnHover) {
			this.root.addEventListener('mouseenter', function () { self.pause('hover'); });
			this.root.addEventListener('mouseleave', function () { self.resume('hover'); });
		}

		// Pausing while off-screen or on a background tab keeps the
		// timer honest and saves the visitor's battery. Each reason is
		// tracked separately, so moving the pointer away does not
		// restart autoplay under someone still tabbing through it.
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
			// Assume off-screen until the observer says otherwise; its
			// first callback fires straight away and clears this.
			this.pause('offscreen');

			new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						self.resume('offscreen');
					} else {
						self.pause('offscreen');
					}
				});
			}, { threshold: 0.15 }).observe(this.root);
		}

		this.bindSwipe();
	};

	Slider.prototype.bindSwipe = function () {
		var self = this;
		var startX = 0;
		var startY = 0;
		var tracking = false;

		var stage = this.root.querySelector('[data-sbvis-stage]');

		if (!stage) {
			return;
		}

		stage.addEventListener('touchstart', function (event) {
			var touch = event.changedTouches[0];
			startX = touch.clientX;
			startY = touch.clientY;
			tracking = true;
		}, { passive: true });

		stage.addEventListener('touchend', function (event) {
			if (!tracking) {
				return;
			}
			tracking = false;

			var touch = event.changedTouches[0];
			var dx = touch.clientX - startX;
			var dy = touch.clientY - startY;

			// Only treat it as a swipe if the gesture was clearly
			// horizontal, so it never hijacks a vertical page scroll.
			if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy) * 1.5) {
				self.step(dx < 0 ? 1 : -1, true);
			}
		}, { passive: true });
	};

	Slider.prototype.step = function (direction, byUser) {
		var next = this.index + direction;
		var last = this.slides.length - 1;

		if (next > last) {
			if (!this.config.loop && !byUser) {
				this.stop();
				return;
			}
			next = 0;
		}

		if (next < 0) {
			next = last;
		}

		this.goTo(next, byUser);
	};

	Slider.prototype.goTo = function (index, byUser) {
		if (isNaN(index) || index === this.index || !this.slides[index]) {
			return;
		}

		var leaving = this.slides[this.index];

		leaving.classList.remove('is-active');
		leaving.classList.add('is-leaving');
		leaving.setAttribute('aria-hidden', 'true');

		window.setTimeout(function () {
			leaving.classList.remove('is-leaving');
		}, this.config.speed || 0);

		this.index = index;

		var entering = this.slides[index];
		entering.classList.add('is-active');
		entering.removeAttribute('aria-hidden');

		this.dots.forEach(function (dot, i) {
			dot.classList.toggle('is-active', i === index);
			dot.setAttribute('aria-selected', i === index ? 'true' : 'false');
			dot.style.removeProperty('--progress');
		});

		// Restart the clock on every change, so a slide the visitor
		// clicked to gets its full time on screen rather than the
		// remainder of the previous slide's.
		if (this.playing) {
			this.play();
		}
	};

	Slider.prototype.play = function () {
		var self = this;

		this.stop();
		this.startedAt = Date.now();

		this.timer = window.setTimeout(function () {
			self.step(1, false);
		}, this.config.delay || 6000);

		this.trackProgress();
	};

	/* Drives the fill on the "progress bars" navigation style. */
	Slider.prototype.trackProgress = function () {
		var self = this;

		if (!this.root.classList.contains('sbvis--nav-bars')) {
			return;
		}

		var tick = function () {
			var dot = self.dots[self.index];

			if (!dot || !self.timer) {
				return;
			}

			var elapsed = (Date.now() - self.startedAt) / (self.config.delay || 6000);
			dot.style.setProperty('--progress', Math.min(1, elapsed).toFixed(3));
			self.rafId = window.requestAnimationFrame(tick);
		};

		this.rafId = window.requestAnimationFrame(tick);
	};

	Slider.prototype.stop = function () {
		if (this.timer) {
			window.clearTimeout(this.timer);
			this.timer = null;
		}
		if (this.rafId) {
			window.cancelAnimationFrame(this.rafId);
			this.rafId = null;
		}
	};

	/*
	 * Pause and resume are reference-counted by reason: autoplay only
	 * restarts once every reason that paused it has cleared. Without
	 * that, a mouseleave would restart the slider while keyboard focus
	 * was still inside it, or while the tab was in the background.
	 */
	Slider.prototype.pause = function (reason) {
		this.paused[reason || 'manual'] = true;

		if (this.playing) {
			this.stop();
		}
	};

	Slider.prototype.resume = function (reason) {
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
		Array.prototype.forEach.call(document.querySelectorAll('.sbvis'), function (root) {
			if (!root.sbvisInstance) {
				root.sbvisInstance = new Slider(root);
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
