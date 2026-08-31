/*
 * Slide editor: the media picker, the click-to-set focal point, the
 * live readouts on the range sliders, and the conditional button
 * position panel.
 */
(function () {
	'use strict';

	function initImageField(field) {
		var focal = field.querySelector('[data-sbvis-focal]');
		var img = field.querySelector('[data-sbvis-focal-img]');
		var marker = field.querySelector('[data-sbvis-marker]');
		var empty = field.querySelector('.sbvis-focal__empty');
		var idInput = field.querySelector('[data-sbvis-image-id]');
		var xInput = field.querySelector('[data-sbvis-focal-x]');
		var yInput = field.querySelector('[data-sbvis-focal-y]');
		var readX = field.querySelector('[data-sbvis-readout-x]');
		var readY = field.querySelector('[data-sbvis-readout-y]');
		var selectBtn = field.querySelector('[data-sbvis-select]');
		var removeBtn = field.querySelector('[data-sbvis-remove]');
		var centreBtn = field.querySelector('[data-sbvis-recentre]');
		var frame = null;

		function setFocal(x, y) {
			x = Math.round(Math.min(100, Math.max(0, x)));
			y = Math.round(Math.min(100, Math.max(0, y)));

			xInput.value = x;
			yInput.value = y;
			marker.style.left = x + '%';
			marker.style.top = y + '%';
			readX.textContent = x;
			readY.textContent = y;
		}

		function showImage(url) {
			if (url) {
				img.src = url;
				img.hidden = false;
				marker.hidden = false;
				empty.hidden = true;
				removeBtn.hidden = false;
			} else {
				img.removeAttribute('src');
				img.hidden = true;
				marker.hidden = true;
				empty.hidden = false;
				removeBtn.hidden = true;
			}
		}

		focal.addEventListener('click', function (event) {
			if (img.hidden) {
				return;
			}

			var rect = img.getBoundingClientRect();

			if (!rect.width || !rect.height) {
				return;
			}

			setFocal(
				((event.clientX - rect.left) / rect.width) * 100,
				((event.clientY - rect.top) / rect.height) * 100
			);
		});

		// Keyboard equivalent, so setting a focal point is not mouse-only.
		focal.setAttribute('tabindex', '0');
		focal.addEventListener('keydown', function (event) {
			var step = event.shiftKey ? 10 : 2;
			var x = parseFloat(xInput.value) || 50;
			var y = parseFloat(yInput.value) || 50;
			var handled = true;

			switch (event.key) {
				case 'ArrowLeft': x -= step; break;
				case 'ArrowRight': x += step; break;
				case 'ArrowUp': y -= step; break;
				case 'ArrowDown': y += step; break;
				default: handled = false;
			}

			if (handled) {
				event.preventDefault();
				setFocal(x, y);
			}
		});

		selectBtn.addEventListener('click', function (event) {
			event.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: (window.sbvisAdmin && window.sbvisAdmin.selectTitle) || 'Choose a slide image',
				button: { text: (window.sbvisAdmin && window.sbvisAdmin.selectButton) || 'Use this image' },
				library: { type: 'image' },
				multiple: false
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				var sizes = attachment.sizes || {};
				var preview = (sizes['sbvis-slide-md'] || sizes.large || sizes.medium_large || sizes.full || {}).url || attachment.url;

				idInput.value = attachment.id;
				showImage(preview);
			});

			frame.open();
		});

		removeBtn.addEventListener('click', function (event) {
			event.preventDefault();
			idInput.value = '';
			showImage('');
		});

		centreBtn.addEventListener('click', function (event) {
			event.preventDefault();
			setFocal(50, 50);
		});
	}

	function initRanges() {
		Array.prototype.forEach.call(document.querySelectorAll('[data-sbvis-range]'), function (input) {
			var out = document.getElementById(input.getAttribute('data-sbvis-range'));
			var suffix = input.getAttribute('data-sbvis-suffix') || '%';

			if (!out) {
				return;
			}

			var update = function () { out.textContent = input.value + suffix; };

			input.addEventListener('input', update);
			update();
		});
	}

	function initToggles() {
		Array.prototype.forEach.call(document.querySelectorAll('[data-sbvis-toggle]'), function (checkbox) {
			var target = document.getElementById(checkbox.getAttribute('data-sbvis-toggle'));

			if (!target) {
				return;
			}

			var update = function () { target.hidden = checkbox.checked; };

			checkbox.addEventListener('change', update);
			update();
		});
	}

	function initAnchors() {
		Array.prototype.forEach.call(document.querySelectorAll('.sbvis-anchor-grid'), function (grid) {
			grid.addEventListener('change', function () {
				Array.prototype.forEach.call(grid.querySelectorAll('.sbvis-anchor'), function (label) {
					label.classList.toggle('is-active', label.querySelector('input').checked);
				});
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		Array.prototype.forEach.call(document.querySelectorAll('[data-sbvis-image]'), initImageField);
		initRanges();
		initToggles();
		initAnchors();
	});
})();
