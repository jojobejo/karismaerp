// Proteksi global agar nilai pada semua input angka (qty, harga, nominal, dll.)
// tidak berubah secara tidak sengaja ketika pengguna melakukan scroll mouse.
(function () {
    if (window.__karismaNumberWheelGuardLoaded) {
        return;
    }
    window.__karismaNumberWheelGuardLoaded = true;

    function isNumberInput(el) {
        return !!(el && el.tagName === 'INPUT' && String(el.type || '').toLowerCase() === 'number');
    }

    // Tangkap event wheel pada fase capture agar nilai tidak sempat berubah oleh browser
    document.addEventListener('wheel', function (e) {
        var active = document.activeElement;
        var target = e.target;

        if (isNumberInput(active)) {
            active.blur();
        }

        if (isNumberInput(target)) {
            target.blur();
            e.preventDefault();
        }
    }, { passive: false, capture: true });
})();

