<?php

declare(strict_types=1);

require_once __DIR__ . '/../ValueObjects/GastoId.php';
require_once __DIR__ . '/../ValueObjects/GastoFecha.php';
require_once __DIR__ . '/../ValueObjects/GastoMonto.php';
require_once __DIR__ . '/../ValueObjects/GastoLugar.php';
require_once __DIR__ . '/../ValueObjects/GastoDescripcion.php';
require_once __DIR__ . '/../ValueObjects/UserName.php';
require_once __DIR__ . '/../Exceptions/InvalidGastoMontoException.php';

class GastoModel
{
    private GastoId $id;
    private GastoFecha $fecha;
    private GastoMonto $valorTotalSinIVA;
    private GastoMonto $ivaTotal;
    private GastoMonto $valorTotalConIVA;
    private UserName $nombreUsuario;
    private GastoLugar $lugar;
    private GastoDescripcion $descripcion;

    public function __construct(
        GastoId $id,
        GastoFecha $fecha,
        GastoMonto $valorTotalSinIVA,
        GastoMonto $ivaTotal,
        GastoMonto $valorTotalConIVA,
        UserName $nombreUsuario,
        GastoLugar $lugar,
        GastoDescripcion $descripcion
    ) {
        $expectedTotal = $valorTotalSinIVA->add($ivaTotal, 'valorTotalConIVA');
        if (!$expectedTotal->equals($valorTotalConIVA)) {
            throw InvalidGastoMontoException::becauseTotalConIvaIsInconsistent(
                $valorTotalSinIVA->value(),
                $ivaTotal->value(),
                $valorTotalConIVA->value()
            );
        }

        $this->id = $id;
        $this->fecha = $fecha;
        $this->valorTotalSinIVA = $valorTotalSinIVA;
        $this->ivaTotal = $ivaTotal;
        $this->valorTotalConIVA = $valorTotalConIVA;
        $this->nombreUsuario = $nombreUsuario;
        $this->lugar = $lugar;
        $this->descripcion = $descripcion;
    }

    public static function create(
        GastoId $id,
        GastoFecha $fecha,
        GastoMonto $valorTotalSinIVA,
        GastoMonto $ivaTotal,
        ?GastoMonto $valorTotalConIVA,
        UserName $nombreUsuario,
        GastoLugar $lugar,
        GastoDescripcion $descripcion
    ): self {
        $resolvedTotal = $valorTotalConIVA ?? $valorTotalSinIVA->add($ivaTotal, 'valorTotalConIVA');

        return new self(
            $id,
            $fecha,
            $valorTotalSinIVA,
            $ivaTotal,
            $resolvedTotal,
            $nombreUsuario,
            $lugar,
            $descripcion
        );
    }

    public function id(): GastoId
    {
        return $this->id;
    }

    public function fecha(): GastoFecha
    {
        return $this->fecha;
    }

    public function valorTotalSinIVA(): GastoMonto
    {
        return $this->valorTotalSinIVA;
    }

    public function ivaTotal(): GastoMonto
    {
        return $this->ivaTotal;
    }

    public function valorTotalConIVA(): GastoMonto
    {
        return $this->valorTotalConIVA;
    }

    public function nombreUsuario(): UserName
    {
        return $this->nombreUsuario;
    }

    public function lugar(): GastoLugar
    {
        return $this->lugar;
    }

    public function descripcion(): GastoDescripcion
    {
        return $this->descripcion;
    }

    public function recalculateAmounts(GastoMonto $nuevoSinIVA, GastoMonto $nuevoIva): self
    {
        return new self(
            $this->id,
            $this->fecha,
            $nuevoSinIVA,
            $nuevoIva,
            $nuevoSinIVA->add($nuevoIva, 'valorTotalConIVA'),
            $this->nombreUsuario,
            $this->lugar,
            $this->descripcion
        );
    }

    public function changeLugar(GastoLugar $nuevoLugar): self
    {
        return new self(
            $this->id,
            $this->fecha,
            $this->valorTotalSinIVA,
            $this->ivaTotal,
            $this->valorTotalConIVA,
            $this->nombreUsuario,
            $nuevoLugar,
            $this->descripcion
        );
    }

    public function changeDescripcion(GastoDescripcion $nuevaDescripcion): self
    {
        return new self(
            $this->id,
            $this->fecha,
            $this->valorTotalSinIVA,
            $this->ivaTotal,
            $this->valorTotalConIVA,
            $this->nombreUsuario,
            $this->lugar,
            $nuevaDescripcion
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array(
            'id' => $this->id->value(),
            'fecha' => $this->fecha->value(),
            'valorTotalSinIVA' => $this->valorTotalSinIVA->value(),
            'ivaTotal' => $this->ivaTotal->value(),
            'valorTotalConIVA' => $this->valorTotalConIVA->value(),
            'nombreUsuario' => $this->nombreUsuario->value(),
            'lugar' => $this->lugar->value(),
            'descripcion' => $this->descripcion->value(),
        );
    }
}
