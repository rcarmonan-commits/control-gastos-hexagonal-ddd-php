<?php

declare(strict_types=1);

final class GastoEntity
{
    private string $id;
    private string $fecha;
    private float $valorTotalSinIVA;
    private float $ivaTotal;
    private float $valorTotalConIVA;
    private string $nombreUsuario;
    private string $lugar;
    private string $descripcion;

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
     * @param array<string, mixed> $row
     */
    public static function fromDatabaseRow(array $row): self
    {
        return new self(
            (string) ($row['id'] ?? ''),
            (string) ($row['fecha'] ?? ''),
            (float) ($row['valor_total_sin_iva'] ?? 0.0),
            (float) ($row['iva_total'] ?? 0.0),
            (float) ($row['valor_total_con_iva'] ?? 0.0),
            (string) ($row['nombre_usuario'] ?? ''),
            (string) ($row['lugar'] ?? ''),
            (string) ($row['descripcion'] ?? '')
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFecha(): string
    {
        return $this->fecha;
    }

    public function getValorTotalSinIVA(): float
    {
        return $this->valorTotalSinIVA;
    }

    public function getIvaTotal(): float
    {
        return $this->ivaTotal;
    }

    public function getValorTotalConIVA(): float
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
