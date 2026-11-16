<?php

declare(strict_types=1);

if (!class_exists('CreateGastoRequest', false)) {
    final class CreateGastoRequest
    {
        public string $id;
        public string $fecha;
        public float|int|string $valorTotalSinIVA;
        public float|int|string $ivaTotal;
        public float|int|string|null $valorTotalConIVA;
        public string $nombreUsuario;
        public string $lugar;
        public string $descripcion;

        /**
         * @param array<string, mixed> $input
         */
        public function __construct(array $input = array())
        {
            $this->id = isset($input['id']) ? trim((string) $input['id']) : '';
            $this->fecha = isset($input['fecha']) ? trim((string) $input['fecha']) : '';
            $this->valorTotalSinIVA = $input['valorTotalSinIVA'] ?? '';
            $this->ivaTotal = $input['ivaTotal'] ?? '';
            $this->valorTotalConIVA = isset($input['valorTotalConIVA']) && trim((string) $input['valorTotalConIVA']) !== ''
                ? $input['valorTotalConIVA']
                : null;
            $this->nombreUsuario = isset($input['nombreUsuario']) ? trim((string) $input['nombreUsuario']) : '';
            $this->lugar = isset($input['lugar']) ? trim((string) $input['lugar']) : '';
            $this->descripcion = isset($input['descripcion']) ? trim((string) $input['descripcion']) : '';
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
}
