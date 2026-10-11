import Alpine from 'alpinejs';

// E-CASSAVA selalu terang: tidak ada mode gelap.
// Baris ini membersihkan sisa preferensi lama agar pengguna
// yang dulu mengaktifkan dark mode kembali ke terang.
try {
    localStorage.removeItem('theme');
    document.documentElement.classList.remove('dark');
    document.documentElement.style.colorScheme = 'light';
} catch (e) {}

window.Alpine = Alpine;

Alpine.start();
