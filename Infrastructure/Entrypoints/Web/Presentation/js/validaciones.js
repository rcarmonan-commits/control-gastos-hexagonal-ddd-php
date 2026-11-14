document.addEventListener('DOMContentLoaded', function () {
    const sinIvaInput = document.getElementById('valorTotalSinIVA');
    const ivaInput = document.getElementById('ivaTotal');
    const conIvaInput = document.getElementById('valorTotalConIVA');
    const calcIvaBtn = document.getElementById('btnCalcularIva19');

    function sincronizarTotalConIva() {
        if (!sinIvaInput || !ivaInput || !conIvaInput) {
            return;
        }
        const sinIva = parseFloat(sinIvaInput.value) || 0;
        const iva = parseFloat(ivaInput.value) || 0;
        conIvaInput.value = (Math.round((sinIva + iva) * 100) / 100).toFixed(2);
    }

    if (sinIvaInput && ivaInput) {
        sinIvaInput.addEventListener('input', sincronizarTotalConIva);
        ivaInput.addEventListener('input', sincronizarTotalConIva);
    }

    if (calcIvaBtn && sinIvaInput && ivaInput) {
        calcIvaBtn.addEventListener('click', function () {
            const sinIva = parseFloat(sinIvaInput.value) || 0;
            const iva19 = Math.round(sinIva * 0.19 * 100) / 100;
            ivaInput.value = iva19.toFixed(2);
            sincronizarTotalConIva();
        });
    }
});
