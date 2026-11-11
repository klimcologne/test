/* Polinis ON/OFF */
(function () {
	'use strict';

	var root = document.documentElement;
	var cfg = window.POLINIS || { storageKey: 'polinis-signal', labels: { on: 'Signal an', off: 'Signal aus' } };
	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

	/* ---------- Schalter ---------- */

	var live = document.createElement('p');
	live.className = 'screen-reader-text';
	live.setAttribute('role', 'status');
	document.body.appendChild(live);

	function current() {
		return root.dataset.signal === 'on' ? 'on' : 'off';
	}

	function syncSwitches() {
		var on = current() === 'on';
		document.querySelectorAll('[data-signal-toggle]').forEach(function (btn) {
			btn.setAttribute('aria-pressed', on ? 'true' : 'false');
		});
	}

	function apply(state) {
		root.dataset.signal = state;
		syncSwitches();
		document.querySelectorAll('[data-signal-toggle]').forEach(function (btn) {
			btn.classList.remove('is-flipping');
			void btn.offsetWidth;
			btn.classList.add('is-flipping');
		});
	}

	function setSignal(state, origin) {
		if (state === current()) {
			return;
		}
		try {
			localStorage.setItem(cfg.storageKey, state);
		} catch (e) {}

		live.textContent = cfg.labels[state] || '';

		if (!document.startViewTransition || reduceMotion.matches) {
			apply(state);
			return;
		}

		var x = window.innerWidth / 2;
		var y = window.innerHeight / 2;
		if (origin) {
			var r = origin.getBoundingClientRect();
			x = r.left + r.width / 2;
			y = r.top + r.height / 2;
		}
		var radius = Math.hypot(Math.max(x, window.innerWidth - x), Math.max(y, window.innerHeight - y));
		root.style.setProperty('--vt-x', x + 'px');
		root.style.setProperty('--vt-y', y + 'px');
		root.style.setProperty('--vt-r', radius + 'px');
		document.startViewTransition(function () {
			apply(state);
		});
	}

	document.addEventListener('click', function (e) {
		var toggle = e.target.closest('[data-signal-toggle]');
		if (toggle) {
			setSignal(current() === 'on' ? 'off' : 'on', toggle.querySelector('.switch__body') || toggle);
			return;
		}
		var set = e.target.closest('[data-signal-set]');
		if (set) {
			setSignal(set.getAttribute('data-signal-set'), set);
			var hero = document.querySelector('.hero');
			if (hero) {
				hero.scrollIntoView({ behavior: reduceMotion.matches ? 'auto' : 'smooth' });
			}
		}
	});

	syncSwitches();

	/* ---------- Menü und Suche ---------- */

	var menuBtn = document.querySelector('[data-menu-toggle]');
	var nav = document.querySelector('[data-nav]');
	var searchBtn = document.querySelector('[data-search-toggle]');
	var searchPanel = document.querySelector('[data-search-panel]');

	function setMenu(open) {
		if (!menuBtn || !nav) {
			return;
		}
		var header = document.querySelector('[data-header]');
		if (header) {
			nav.style.setProperty('--nav-offset', Math.max(0, header.getBoundingClientRect().top) + 'px');
		}
		nav.classList.toggle('is-open', open);
		menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
	}

	function setSearch(open) {
		if (!searchBtn || !searchPanel) {
			return;
		}
		searchPanel.hidden = !open;
		searchBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
		if (open) {
			var input = searchPanel.querySelector('input[type="search"]');
			if (input) {
				input.focus();
			}
		}
	}

	if (menuBtn) {
		menuBtn.addEventListener('click', function () {
			setSearch(false);
			setMenu(menuBtn.getAttribute('aria-expanded') !== 'true');
		});
	}

	if (searchBtn) {
		searchBtn.addEventListener('click', function () {
			setMenu(false);
			setSearch(searchPanel.hidden);
		});
	}

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			if (nav && nav.classList.contains('is-open')) {
				setMenu(false);
				menuBtn.focus();
			}
			if (searchPanel && !searchPanel.hidden) {
				setSearch(false);
				searchBtn.focus();
			}
		}
	});

	/* ---------- Varianten als Knöpfe ---------- */

	var colorMap = {
		'schwarz': '#111014', 'weiß': '#f4f3f1', 'sand': '#c8a77a', 'carolina-blau': '#9ec3e6',
		'athletisch heather': '#a7a7a9', 'white heather': '#e7e6e4', 'heather grau': '#9d9ea0',
		'stein': '#d9cfb6', 'french navy': '#1d2540', 'navy': '#1d2540', 'pine green': '#1d4a3c',
		'fichte': '#1d4a3c', 'kobalt': '#2f45a8', 'kastanie': '#6b3a26', 'clay': '#a4552e',
		'cypress': '#6e7651', 'desert dust': '#d8c6a8', 'fraiche peche': '#f2c6ad', 'cotton pink': '#f3c3cf',
		'lavendel': '#c9b9e6', 'faded bone': '#e6e0d2', 'faded eucalyptus': '#b8c4a8', 'faded khaki': '#cdbd97',
		'faded grey': '#8f8f8c', 'faded black': '#2a292b', 'faded coal': '#46484b', 'faded indigo': '#4f6584',
		'dunkelgrau': '#46474b', 'silber': '#a9abae', 'natürlich/ schwarz': '#e9e2cf'
	};

	function buildSwatches(form) {
		form.querySelectorAll('.variations select').forEach(function (select) {
			var isColor = /farbe/i.test(select.name);
			var cell = select.closest('td');
			var row = select.closest('tr');
			var label = row ? row.querySelector('th label') : null;
			var valueEl = document.createElement('span');
			valueEl.className = 'swatch-value';
			if (label) {
				label.appendChild(document.createTextNode(' '));
				label.appendChild(valueEl);
			}

			var group = document.createElement('div');
			group.className = 'swatches';
			group.setAttribute('role', 'group');
			group.setAttribute('aria-label', label ? label.firstChild.textContent.trim() : select.name);

			Array.prototype.forEach.call(select.options, function (opt) {
				if (!opt.value) {
					return;
				}
				var b = document.createElement('button');
				b.type = 'button';
				b.className = 'swatch' + (isColor ? ' swatch--color' : '');
				b.dataset.value = opt.value;
				b.setAttribute('aria-pressed', 'false');
				if (isColor) {
					var chip = document.createElement('span');
					chip.style.setProperty('--dot', colorMap[opt.text.trim().toLowerCase()] || '#888');
					b.appendChild(chip);
					b.title = opt.text;
					b.setAttribute('aria-label', opt.text);
				} else {
					b.textContent = opt.text;
				}
				b.addEventListener('click', function () {
					select.value = select.value === opt.value ? '' : opt.value;
					select.dispatchEvent(new Event('change', { bubbles: true }));
					sync();
				});
				group.appendChild(b);
			});

			cell.classList.add('has-swatches');
			cell.insertBefore(group, select);

			function sync() {
				group.querySelectorAll('.swatch').forEach(function (b) {
					var opt = select.querySelector('option[value="' + CSS.escape(b.dataset.value) + '"]');
					b.disabled = !opt || opt.disabled;
					b.setAttribute('aria-pressed', select.value === b.dataset.value ? 'true' : 'false');
				});
				var sel = select.options[select.selectedIndex];
				valueEl.textContent = select.value && sel ? '· ' + sel.text : '';
			}

			select.addEventListener('change', sync);
			if (window.jQuery) {
				window.jQuery(form).on('woocommerce_update_variation_values reset_data', function () {
					window.setTimeout(sync, 0);
				});
			}
			sync();
		});
	}

	document.querySelectorAll('.variations_form').forEach(buildSwatches);

	/* ---------- Bildwechsel bei Farbwahl ---------- */

	function bindVariationImage(form) {
		var img = document.querySelector('.pdp-gallery .wp-post-image');
		if (!img) {
			return;
		}
		var original = { src: img.getAttribute('src'), srcset: img.getAttribute('srcset') || '', alt: img.alt };
		var track = img.closest('.pdp-gallery__track');

		function show(src, srcset, alt) {
			if (img.getAttribute('src') === src) {
				return;
			}
			img.setAttribute('src', src);
			if (srcset) {
				img.setAttribute('srcset', srcset);
			} else {
				img.removeAttribute('srcset');
			}
			img.alt = alt || original.alt;
			if (track && track.scrollLeft > 0) {
				track.scrollTo({ left: 0, behavior: reduceMotion.matches ? 'auto' : 'smooth' });
			}
		}

		var colorSelect = form.querySelector('select[name="attribute_pa_farbe"]');
		var variations = [];
		try {
			variations = JSON.parse(form.dataset.product_variations || '[]') || [];
		} catch (err) {}

		/* Farbe gewählt, Größe noch nicht: trotzdem die passende Farbe zeigen. */
		function showForColor() {
			var match = colorSelect && colorSelect.value && variations.find(function (v) {
				return v.attributes.attribute_pa_farbe === colorSelect.value && v.image && v.image.src;
			});
			if (match) {
				show(match.image.src, match.image.srcset, match.image.alt);
			} else {
				show(original.src, original.srcset, original.alt);
			}
		}

		if (window.jQuery) {
			window.jQuery(form)
				.on('found_variation', function (e, variation) {
					if (variation && variation.image && variation.image.src) {
						show(variation.image.src, variation.image.srcset, variation.image.alt);
					}
				})
				.on('reset_image', function () {
					window.setTimeout(showForColor, 0);
				});
		}

		if (colorSelect) {
			colorSelect.addEventListener('change', function () {
				window.setTimeout(showForColor, 0);
			});
		}
	}

	document.querySelectorAll('.variations_form').forEach(bindVariationImage);
})();
