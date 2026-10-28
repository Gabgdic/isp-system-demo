import './bootstrap';

const themeToggle = document.getElementById('adminThemeToggle');

if (themeToggle) {
	const moonIcon = themeToggle.querySelector('[data-theme-icon="moon"]');
	const sunIcon = themeToggle.querySelector('[data-theme-icon="sun"]');

	const syncThemeToggle = () => {
		const isDark = document.documentElement.classList.contains('dark');

		moonIcon?.classList.toggle('hidden', isDark);
		sunIcon?.classList.toggle('hidden', !isDark);
		themeToggle.setAttribute('aria-pressed', String(isDark));
		themeToggle.setAttribute('aria-label', isDark ? 'Enable light mode' : 'Enable dark mode');
		themeToggle.setAttribute('title', isDark ? 'Enable light mode' : 'Enable dark mode');
	};

	syncThemeToggle();

	themeToggle.addEventListener('click', () => {
		const isDark = document.documentElement.classList.toggle('dark');

		try {
			localStorage.setItem('admin-theme', isDark ? 'dark' : 'light');
		} catch (error) {}

		syncThemeToggle();
		document.dispatchEvent(new CustomEvent('admin-theme-change', { detail: { isDark } }));
	});
}
