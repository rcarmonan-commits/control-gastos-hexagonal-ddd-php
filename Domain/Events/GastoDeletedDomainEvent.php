<?php

declare(strict_types=1);

require_once __DIR__ . '/DomainEvent.php';
require_once __DIR__ . '/../ValueObjects/GastoId.php';

class GastoDeletedDomainEvent extends DomainEvent
{
    private GastoId $gastoId;

    public function __construct(GastoId $gastoId)
    {
        parent::__construct('gasto.deleted');
        $this->gastoId = $gastoId;
    }

    public function gastoId(): GastoId
    {
        return $this->gastoId;
    }

    public function payload(): array
    {
        return array(
            'id' => $this->gastoId->value(),
        );
    }
}
