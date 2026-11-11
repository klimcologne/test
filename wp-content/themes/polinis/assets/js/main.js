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


	/* ---------- Warenkorb-Leiste ---------- */

	var drawer = document.querySelector('[data-drawer]');
	var lastFocus = null;

	function setDrawer(open) {
		if (!drawer) {
			return false;
		}
		document.documentElement.classList.toggle('drawer-open', open);
		if (open) {
			lastFocus = document.activeElement;
			drawer.removeAttribute('inert');
			drawer.focus({ preventScroll: true });
			var first = drawer.querySelector('button, a');
			if (first) {
				first.focus({ preventScroll: true });
			}
		} else {
			drawer.setAttribute('inert', '');
			if (lastFocus && lastFocus.focus) {
				lastFocus.focus({ preventScroll: true });
			}
		}
		return true;
	}

	document.addEventListener('click', function (e) {
		if (e.target.closest('[data-drawer-open]') && drawer) {
			e.preventDefault();
			setDrawer(true);
		} else if (e.target.closest('[data-drawer-close]')) {
			setDrawer(false);
		}
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && document.documentElement.classList.contains('drawer-open')) {
			setDrawer(false);
		}
	});

	function applyFragments(fragments) {
		Object.keys(fragments || {}).forEach(function (sel) {
			document.querySelectorAll(sel).forEach(function (el) {
				var tpl = document.createElement('template');
				tpl.innerHTML = fragments[sel].trim();
				if (tpl.content.firstElementChild) {
					el.replaceWith(tpl.content.firstElementChild.cloneNode(true));
				}
			});
		});
	}

	function addToCart(productId, quantity, note) {
		if (!cfg.wcAjax) {
			return Promise.reject(new Error('no-ajax'));
		}
		var body = new URLSearchParams();
		body.set('product_id', productId);
		body.set('quantity', quantity || 1);
		return fetch(cfg.wcAjax.replace('%%endpoint%%', 'add_to_cart'), {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		}).then(function (r) {
			return r.json();
		}).then(function (data) {
			if (!data || data.error || !data.fragments) {
				throw new Error('add-failed');
			}
			applyFragments(data.fragments);
			var added = document.querySelector('[data-drawer-added]');
			if (added) {
				added.textContent = note || '';
			}
			setDrawer(true);
			return data;
		});
	}

	/* Quick Add auf der Produktkarte */
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-quick-add]');
		if (!btn) {
			return;
		}
		e.preventDefault();
		var card = btn.closest('.card');
		var name = card ? card.querySelector('.card__title').textContent.trim() : '';
		btn.classList.add('is-loading');
		btn.disabled = true;
		addToCart(btn.getAttribute('data-quick-add'), 1, name + ' · ' + btn.getAttribute('data-label') + ' ist im Warenkorb.')
			.catch(function () {
				var link = card && card.querySelector('.card__title a');
				if (link) {
					window.location.href = link.href;
				}
			})
			.finally(function () {
				btn.classList.remove('is-loading');
				btn.disabled = false;
			});
	});

	/* Produktseite: ohne Neuladen in den Warenkorb */
	document.addEventListener('submit', function (e) {
		var form = e.target.closest('form.cart');
		if (!form || !cfg.wcAjax || form.dataset.sending) {
			return;
		}
		var variation = form.querySelector('input[name="variation_id"]');
		var simple = form.querySelector('[name="add-to-cart"]');
		var id = variation && variation.value && variation.value !== '0' ? variation.value : (!variation && simple ? simple.value : '');
		if (!id) {
			return;
		}
		e.preventDefault();
		var qty = form.querySelector('input.qty');
		var title = document.querySelector('.product_title');
		var picked = Array.prototype.map.call(form.querySelectorAll('.variations select'), function (sel) {
			return sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '';
		}).filter(Boolean).join(' / ');
		var btn = form.querySelector('.single_add_to_cart_button');
		if (btn) {
			btn.classList.add('is-loading');
		}
		form.dataset.sending = '1';
		addToCart(id, qty ? qty.value : 1, (title ? title.textContent.trim() : '') + (picked ? ' · ' + picked : '') + ' ist im Warenkorb.')
			.catch(function () {
				form.submit();
			})
			.finally(function () {
				delete form.dataset.sending;
				if (btn) {
					btn.classList.remove('is-loading');
				}
			});
	});

	/* ---------- Drop-Countdown ---------- */

	document.querySelectorAll('[data-countdown]').forEach(function (el) {
		var target = parseInt(el.getAttribute('data-countdown'), 10) * 1000;
		function pad(n) {
			return (n < 10 ? '0' : '') + n;
		}
		function tick() {
			var left = Math.max(0, target - Date.now());
			var sec = Math.floor(left / 1000);
			var parts = { d: Math.floor(sec / 86400), h: Math.floor(sec % 86400 / 3600), m: Math.floor(sec % 3600 / 60), s: sec % 60 };
			Object.keys(parts).forEach(function (k) {
				var b = el.querySelector('[data-cd="' + k + '"]');
				if (b) {
					b.textContent = pad(parts[k]);
				}
			});
			if (left > 0) {
				window.setTimeout(tick, 1000 - (Date.now() % 1000));
			}
		}
		tick();
	});

	/* ---------- Kaufleiste am Handy ---------- */

	var buybar = document.querySelector('[data-buybar]');
	var buyForm = document.querySelector('form.cart');
	if (buybar && buyForm && 'IntersectionObserver' in window) {
		var mainBtn = buyForm.querySelector('.single_add_to_cart_button');
		var barBtn = buybar.querySelector('[data-buybar-btn]');
		var barPrice = buybar.querySelector('[data-buybar-price]');
		function ready() {
			return mainBtn && !mainBtn.classList.contains('disabled');
		}
		function syncBar() {
			barBtn.textContent = ready() ? (mainBtn.textContent.trim() || 'In den Warenkorb') : 'Größe wählen';
			var vp = buyForm.querySelector('.woocommerce-variation-price .price');
			if (vp && barPrice) {
				barPrice.innerHTML = vp.innerHTML;
			}
		}
		new IntersectionObserver(function (entries) {
			buybar.classList.toggle('is-visible', !entries[0].isIntersecting);
			buybar.setAttribute('aria-hidden', buybar.classList.contains('is-visible') ? 'false' : 'true');
			barBtn.tabIndex = buybar.classList.contains('is-visible') ? 0 : -1;
		}).observe(mainBtn || buyForm);
		barBtn.addEventListener('click', function () {
			if (ready()) {
				buyForm.requestSubmit ? buyForm.requestSubmit(mainBtn) : mainBtn.click();
			} else {
				var sw = buyForm.querySelector('.variations');
				(sw || buyForm).scrollIntoView({ behavior: reduceMotion.matches ? 'auto' : 'smooth', block: 'center' });
			}
		});
		buyForm.addEventListener('change', function () {
			window.setTimeout(syncBar, 50);
		});
		if (window.jQuery) {
			window.jQuery(buyForm).on('found_variation reset_data hide_variation show_variation', function () {
				window.setTimeout(syncBar, 50);
			});
		}
		syncBar();
	}
})();
