<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Common/EnvLoader.php';

final class TaxReferenceRestClient
{
    private float $fallbackRate;

    public function __construct(?float $fallbackRate = null)
    {
        $configured = EnvLoader::get('TAX_DEFAULT_IVA_RATE', '0.19');
        $this->fallbackRate = $fallbackRate ?? (is_numeric($configured) ? (float) $configured : 0.19);
    }

    /**
     * Obtiene la tarifa referencial de IVA colombiano (19%) con degradacion elegante sin red.
     *
     * @return array{country: string, standardIvaRate: float, source: string}
     */
    public function fetchCurrentIvaReference(): array
    {
        return array(
            'country' => 'CO',
            'standardIvaRate' => round($this->fallbackRate, 4),
            'source' => 'config-local-colombia-estatuto-tributario',
        );
    }
}
