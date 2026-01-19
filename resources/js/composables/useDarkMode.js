import { ref, watch, onMounted } from 'vue';

const isDark = ref(false);

export function useDarkMode() {
    const toggleDarkMode = () => {
        isDark.value = !isDark.value;
        updateDarkMode();
    };

    const updateDarkMode = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('darkMode', 'true');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('darkMode', 'false');
        }
    };

    const initDarkMode = () => {
        const savedMode = localStorage.getItem('darkMode');
        if (savedMode === 'true') {
            isDark.value = true;
        } else if (savedMode === 'false') {
            isDark.value = false;
        } else {
            // Check system preference
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        updateDarkMode();
    };

    onMounted(() => {
        initDarkMode();
    });

    return {
        isDark,
        toggleDarkMode,
    };
}
