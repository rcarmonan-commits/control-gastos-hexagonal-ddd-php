<?php

declare(strict_types=1);

final class GastoPersistenceDto
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
}
