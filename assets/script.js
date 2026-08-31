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
		setMenuState(true);
		menuBtn?.focus();
	});
}

if ('serviceWorker' in navigator) {
	window.addEventListener('load', () => {
		navigator.serviceWorker.register('/service-worker.js').catch(() => {});
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
			? `${visibleCount} jenis layanan ditemukan. Ketuk layanan untuk melihat detail.`
			: 'Layanan belum ditemukan. Coba kata yang lebih umum atau tanyakan kepada pengurus.';
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
