<?php

declare(strict_types=1);

final class CreateGastoCommand
{
    private string $id;
    private string $fecha;
    private float|int|string $valorTotalSinIVA;
    private float|int|string $ivaTotal;
    private float|int|string|null $valorTotalConIVA;
    private string $nombreUsuario;
    private string $lugar;
    private string $descripcion;

    public function __construct(
        string $id,
        string $fecha,
        float|int|string $valorTotalSinIVA,
        float|int|string $ivaTotal,
        float|int|string|null $valorTotalConIVA,
        string $nombreUsuario,
        string $lugar,
        string $descripcion
    ) {
        $this->id = trim($id);
        $this->fecha = trim($fecha);
        $this->valorTotalSinIVA = $valorTotalSinIVA;
        $this->ivaTotal = $ivaTotal;
        $this->valorTotalConIVA = ($valorTotalConIVA === '' || $valorTotalConIVA === null) ? null : $valorTotalConIVA;
        $this->nombreUsuario = trim($nombreUsuario);
        $this->lugar = trim($lugar);
        $this->descripcion = trim($descripcion);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFecha(): string
    {
        return $this->fecha;
    }

    public function getValorTotalSinIVA(): float|int|string
    {
        return $this->valorTotalSinIVA;
    }

    public function getIvaTotal(): float|int|string
    {
        return $this->ivaTotal;
    }

    public function getValorTotalConIVA(): float|int|string|null
    {
        return $this->valorTotalConIVA;
    }

    public function getNombreUsuario(): string
    {
        return $this->nombreUsuario;
    }

    public function getLugar(): string
    {
        return $this->lugar;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }
}
