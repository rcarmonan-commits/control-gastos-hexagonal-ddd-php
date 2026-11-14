<?php

declare(strict_types=1);

final class GastoResponse
{
    public string $id;
    public string $fecha;
    public float $valorTotalSinIVA;
    public float $ivaTotal;
    public float $valorTotalConIVA;
    public string $nombreUsuario;
    public string $lugar;
    public string $descripcion;

    public function __construct(
        string $id,
        string $fecha,
        float $valorTotalSinIVA,
        float $ivaTotal,
        float $valorTotalConIVA,
        string $nombreUsuario,
        string $lugar,
        string $descripcion
    ) {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->valorTotalSinIVA = $valorTotalSinIVA;
        $this->ivaTotal = $ivaTotal;
        $this->valorTotalConIVA = $valorTotalConIVA;
        $this->nombreUsuario = $nombreUsuario;
        $this->lugar = $lugar;
        $this->descripcion = $descripcion;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array(
            'id' => $this->id,
            'fecha' => $this->fecha,
            'valorTotalSinIVA' => $this->valorTotalSinIVA,
            'ivaTotal' => $this->ivaTotal,
            'valorTotalConIVA' => $this->valorTotalConIVA,
            'nombreUsuario' => $this->nombreUsuario,
            'lugar' => $this->lugar,
            'descripcion' => $this->descripcion,
        );
    }
}
