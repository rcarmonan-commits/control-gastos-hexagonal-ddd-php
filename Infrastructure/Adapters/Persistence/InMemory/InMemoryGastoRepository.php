<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../Application/Ports/Out/SaveGastoPort.php';
require_once __DIR__ . '/../../../../Application/Ports/Out/GetGastoByIdPort.php';
require_once __DIR__ . '/../../../../Application/Ports/Out/GetAllGastosPort.php';
require_once __DIR__ . '/../../../../Application/Ports/Out/UpdateGastoPort.php';
require_once __DIR__ . '/../../../../Application/Ports/Out/DeleteGastoPort.php';

final class InMemoryGastoRepository implements
    SaveGastoPort,
    GetGastoByIdPort,
    GetAllGastosPort,
    UpdateGastoPort,
    DeleteGastoPort
{
    /** @var array<string, GastoModel> */
    private array $items = array();

    private ?string $storageFile;

    public function __construct(?string $storageFile = null, bool $seedDefaults = true)
    {
        $this->storageFile = $storageFile;

        if ($this->storageFile !== null && is_file($this->storageFile)) {
            $this->loadFromDisk();
        } elseif ($seedDefaults) {
            $this->seed();
            $this->flushToDisk();
        }
    }

    public function save(GastoModel $gasto): GastoModel
    {
        $this->items[$gasto->id()->value()] = $gasto;
        $this->flushToDisk();
        return $gasto;
    }

    public function getById(GastoId $gastoId): ?GastoModel
    {
        return $this->items[$gastoId->value()] ?? null;
    }

    public function getAll(?string $search = null, ?string $nombreUsuario = null): array
    {
        $values = array_values($this->items);
        $filtered = array();

        foreach ($values as $gasto) {
            if ($nombreUsuario !== null && trim($nombreUsuario) !== '') {
                if (mb_strtolower($gasto->nombreUsuario()->value()) !== mb_strtolower(trim($nombreUsuario))) {
                    continue;
                }
            }

            if ($search !== null && trim($search) !== '') {
                $needle = mb_strtolower(trim($search));
                $haystack = mb_strtolower(
                    $gasto->id()->value() . ' ' .
                    $gasto->fecha()->value() . ' ' .
                    $gasto->nombreUsuario()->value() . ' ' .
                    $gasto->lugar()->value() . ' ' .
                    $gasto->descripcion()->value()
                );

                if (!str_contains($haystack, $needle)) {
                    continue;
                }
            }

            $filtered[] = $gasto;
        }

        usort(
            $filtered,
            static fn(GastoModel $a, GastoModel $b): int => strcmp($b->fecha()->value(), $a->fecha()->value())
        );

        return $filtered;
    }

    public function update(GastoModel $gasto): GastoModel
    {
        $this->items[$gasto->id()->value()] = $gasto;
        $this->flushToDisk();
        return $gasto;
    }

    public function delete(GastoId $gastoId): void
    {
        unset($this->items[$gastoId->value()]);
        $this->flushToDisk();
    }

    private function seed(): void
    {
        $gasto1 = GastoModel::create(
            new GastoId('GAS-001'),
            new GastoFecha('2026-08-20'),
            new GastoMonto(150000.00, 'valorTotalSinIVA'),
            new GastoMonto(28500.00, 'ivaTotal'),
            new GastoMonto(178500.00, 'valorTotalConIVA'),
            new UserName('Administrador'),
            new GastoLugar('Papeleria Panamericana Cartagena'),
            new GastoDescripcion('Compra de suministros de oficina y papeleria institucional')
        );

        $gasto2 = GastoModel::create(
            new GastoId('GAS-002'),
            new GastoFecha('2026-09-14'),
            new GastoMonto(320000.00, 'valorTotalSinIVA'),
            new GastoMonto(60800.00, 'ivaTotal'),
            new GastoMonto(380800.00, 'valorTotalConIVA'),
            new UserName('Rosary Carmona'),
            new GastoLugar('Centro de Convenciones Cartagena'),
            new GastoDescripcion('Inscripcion a jornada academica de ingenieria de software')
        );

        $this->items[$gasto1->id()->value()] = $gasto1;
        $this->items[$gasto2->id()->value()] = $gasto2;
    }

    private function loadFromDisk(): void
    {
        if ($this->storageFile === null || !is_file($this->storageFile)) {
            return;
        }

        $raw = @file_get_contents($this->storageFile);
        if ($raw === false) {
            return;
        }

        $rows = json_decode($raw, true);
        if (!is_array($rows)) {
            return;
        }

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $gasto = new GastoModel(
                new GastoId((string) ($row['id'] ?? '')),
                new GastoFecha((string) ($row['fecha'] ?? '')),
                new GastoMonto((float) ($row['valorTotalSinIVA'] ?? 0.0), 'valorTotalSinIVA'),
                new GastoMonto((float) ($row['ivaTotal'] ?? 0.0), 'ivaTotal'),
                new GastoMonto((float) ($row['valorTotalConIVA'] ?? 0.0), 'valorTotalConIVA'),
                new UserName((string) ($row['nombreUsuario'] ?? '')),
                new GastoLugar((string) ($row['lugar'] ?? '')),
                new GastoDescripcion((string) ($row['descripcion'] ?? ''))
            );

            $this->items[$gasto->id()->value()] = $gasto;
        }
    }

    private function flushToDisk(): void
    {
        if ($this->storageFile === null) {
            return;
        }

        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $payload = array();
        foreach ($this->items as $gasto) {
            $payload[] = $gasto->toArray();
        }

        @file_put_contents($this->storageFile, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
}
