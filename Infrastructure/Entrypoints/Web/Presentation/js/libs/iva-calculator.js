(function (global) {
    'use strict';

    global.HexagonalIvaCalculator = {
        calculateTotalWithIva: function (valorSinIva, ivaTotal) {
            var baseCents = Math.round((parseFloat(valorSinIva) || 0) * 100);
            var ivaCents = Math.round((parseFloat(ivaTotal) || 0) * 100);
            return ((baseCents + ivaCents) / 100).toFixed(2);
        }
    };
})(window);