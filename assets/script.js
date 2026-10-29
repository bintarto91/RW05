const menuBtn = document.getElementById('menuBtn');
const menu = document.getElementById('menu');
const mobileMenuTrigger = document.querySelector('[data-mobile-menu-trigger]');
const topbar = document.querySelector('.topbar');
const navLinks = Array.from(document.querySelectorAll('.menu a[href^="#"]'));
const sections = Array.from(document.querySelectorAll('main section[id]'));
const revealItems = document.querySelectorAll('[data-reveal]');

const setMenuState = (isOpen) => {
	if (!menu || !menuBtn) {
		return;
	}

	menu.classList.toggle('show', isOpen);
	menuBtn.setAttribute('aria-expanded', String(isOpen));
	mobileMenuTrigger?.setAttribute('aria-expanded', String(isOpen));
	mobileMenuTrigger?.setAttribute('aria-label', isOpen ? 'Tutup menu utama' : 'Buka menu utama');
	document.body.classList.toggle('menu-open', isOpen);
};

if (menuBtn && menu) {
	menuBtn.addEventListener('click', () => {
		setMenuState(!menu.classList.contains('show'));
	});

	navLinks.forEach((link) => {
		link.addEventListener('click', () => setMenuState(false));
	});

	document.addEventListener('click', (event) => {
		if (!menu.classList.contains('show')) {
			return;
		}

		if (!menu.contains(event.target)
			&& !menuBtn.contains(event.target)
			&& !mobileMenuTrigger?.contains(event.target)) {
			setMenuState(false);
		}
	});

	window.addEventListener('resize', () => {
		if (window.innerWidth > 900) {
			setMenuState(false);
		}
	});
}

const syncActiveSection = () => {
	if (!sections.length || !navLinks.length) {
		return;
	}

	const scrollPosition = window.scrollY + 140;
	let currentId = sections[0].id;

	sections.forEach((section) => {
		if (section.offsetTop <= scrollPosition) {
			currentId = section.id;
		}
	});

	navLinks.forEach((link) => {
		link.classList.toggle('is-active', link.getAttribute('href') === `#${currentId}`);
	});
};

const syncTopbar = () => {
	if (!topbar) {
		return;
	}

	topbar.classList.toggle('is-scrolled', window.scrollY > 18);
};

syncActiveSection();
syncTopbar();

window.addEventListener('scroll', () => {
	syncActiveSection();
	syncTopbar();
}, { passive: true });

if ('IntersectionObserver' in window) {
	const observer = new IntersectionObserver((entries) => {
		entries.forEach((entry) => {
			if (!entry.isIntersecting) {
				return;
			}

			entry.target.classList.add('visible');
			observer.unobserve(entry.target);
		});
	}, {
		threshold: 0.15,
		rootMargin: '0px 0px -40px 0px',
	});

	revealItems.forEach((item) => observer.observe(item));
} else {
	revealItems.forEach((item) => item.classList.add('visible'));
}

if (mobileMenuTrigger && menu) {
	mobileMenuTrigger.addEventListener('click', () => {
		setMenuState(!menu.classList.contains('show'));
	});
}

if ('serviceWorker' in navigator) {
	window.addEventListener('load', () => {
		let refreshing = false;
		navigator.serviceWorker.addEventListener('controllerchange', () => {
			if (refreshing) return;
			refreshing = true;
			window.location.reload();
		});
		navigator.serviceWorker.register('/service-worker.js', { updateViaCache: 'none' })
			.then((registration) => registration.update())
			.catch(() => {});
	});
}

const serviceSearch = document.getElementById('serviceSearch');
const serviceSearchClear = document.getElementById('serviceSearchClear');
const serviceSearchResult = document.getElementById('serviceSearchResult');
const serviceItems = Array.from(document.querySelectorAll('[data-service-item]'));

const filterServices = () => {
	if (!serviceSearch || !serviceItems.length) {
		return;
	}

	const query = serviceSearch.value.trim().toLocaleLowerCase('id');
	let visibleCount = 0;

	serviceItems.forEach((item) => {
		const searchableText = (item.dataset.serviceName || item.textContent || '').toLocaleLowerCase('id');
		const isVisible = query === '' || searchableText.includes(query);
		item.hidden = !isVisible;
		if (isVisible) {
			visibleCount += 1;
		}
	});

	if (serviceSearchResult) {
		serviceSearchResult.textContent = visibleCount > 0
			? `${visibleCount} jenis surat ditemukan. Ketuk surat untuk melihat detail.`
			: 'Jenis surat belum ditemukan. Coba kata yang lebih umum atau tanyakan kepada pengurus.';
	}
};

if (serviceSearch) {
	serviceSearch.addEventListener('input', filterServices);
}

if (serviceSearchClear && serviceSearch) {
	serviceSearchClear.addEventListener('click', () => {
		serviceSearch.value = '';
		filterServices();
		serviceSearch.focus();
	});
}

if (window.location.hash) {
	const hashTarget = document.querySelector(window.location.hash);
	if (hashTarget instanceof HTMLDetailsElement) {
			hashTarget.open = true;
	}
}


// PWA Install Prompt handling
let deferredPrompt = null;
const pwaBanner = document.getElementById('pwaInstallBanner');
const pwaInstallBtn = document.getElementById('pwaInstallBtn');
const pwaDismissBtn = document.getElementById('pwaDismissBtn');

window.addEventListener('beforeinstallprompt', (e) => {
	e.preventDefault();
	deferredPrompt = e;

	const dismissedAt = localStorage.getItem('pwa_dismissed');
	const now = Date.now();
	if (dismissedAt && (now - Number(dismissedAt)) < 7 * 24 * 60 * 60 * 1000) {
		return;
	}

	if (pwaBanner) {
		pwaBanner.hidden = false;
	}
});

if (pwaInstallBtn) {
	pwaInstallBtn.addEventListener('click', async () => {
		if (!deferredPrompt) return;
		pwaInstallBtn.disabled = true;
		await deferredPrompt.prompt();
		const { outcome } = await deferredPrompt.userChoice;
		if (outcome === 'accepted' && pwaBanner) {
			pwaBanner.hidden = true;
		}
		deferredPrompt = null;
		pwaInstallBtn.disabled = false;
	});
}

if (pwaDismissBtn && pwaBanner) {
	pwaDismissBtn.addEventListener('click', () => {
		pwaBanner.hidden = true;
		localStorage.setItem('pwa_dismissed', String(Date.now()));
	});
}

window.addEventListener('appinstalled', () => {
	if (pwaBanner) pwaBanner.hidden = true;
	deferredPrompt = null;
});

// Copy to Clipboard Utility
document.querySelectorAll('[data-copy-target]').forEach((btn) => {
	btn.addEventListener('click', async () => {
		const targetSelector = btn.getAttribute('data-copy-target');
		const targetEl = targetSelector ? document.querySelector(targetSelector) : null;
		if (!targetEl) return;

		const textToCopy = targetEl.textContent.trim();
		const originalHtml = btn.innerHTML;

		try {
			if (navigator.clipboard && window.isSecureContext) {
				await navigator.clipboard.writeText(textToCopy);
			} else {
				const tempInput = document.createElement('textarea');
				tempInput.value = textToCopy;
				tempInput.style.position = 'fixed';
				tempInput.style.opacity = '0';
				document.body.appendChild(tempInput);
				tempInput.select();
				document.execCommand('copy');
				document.body.removeChild(tempInput);
			}

			btn.innerHTML = '<span>✓</span> Tersalin!';
			btn.classList.add('is-copied');
			setTimeout(() => {
				btn.innerHTML = originalHtml;
				btn.classList.remove('is-copied');
			}, 2500);
		} catch (err) {
			btn.textContent = 'Gagal salin';
			setTimeout(() => { btn.innerHTML = originalHtml; }, 2000);
		}
	});
});

// Mode Anonim pada Form Aspirasi
const isAnonimCheckbox = document.getElementById('isAnonimCheckbox');
const aspirasiNamaInput = document.getElementById('aspirasiNamaInput');
const aspirasiNoHpInput = document.getElementById('aspirasiNoHpInput');

if (isAnonimCheckbox && aspirasiNamaInput) {
	isAnonimCheckbox.addEventListener('change', () => {
		if (isAnonimCheckbox.checked) {
			aspirasiNamaInput.dataset.originalVal = aspirasiNamaInput.value;
			aspirasiNamaInput.value = 'Warga RW 05 (Anonim)';
			aspirasiNamaInput.readOnly = true;
			aspirasiNamaInput.classList.add('input-muted');
			if (aspirasiNoHpInput) {
				aspirasiNoHpInput.placeholder = 'Boleh dikosongkan jika anonim';
			}
		} else {
			aspirasiNamaInput.value = aspirasiNamaInput.dataset.originalVal || '';
			aspirasiNamaInput.readOnly = false;
			aspirasiNamaInput.classList.remove('input-muted');
			if (aspirasiNoHpInput) {
				aspirasiNoHpInput.placeholder = '08xx';
			}
		}
	});
}
