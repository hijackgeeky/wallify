
const themeToggle = document.getElementById('themeToggle');
const body = document.body;

const currentTheme = localStorage.getItem('theme');
if (currentTheme) {
    body.classList.add(currentTheme);
    themeToggle.checked = currentTheme === 'dark-theme';
}

themeToggle.addEventListener('change', () => {
    if (themeToggle.checked) {
        body.classList.remove('light-theme');
        body.classList.add('dark-theme');
        localStorage.setItem('theme', 'dark-theme');
    } else {
        body.classList.remove('dark-theme');
        body.classList.add('light-theme');
        localStorage.setItem('theme', 'light-theme');
    }
});
