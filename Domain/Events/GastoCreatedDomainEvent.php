<?php

declare(strict_types=1);

require_once __DIR__ . '/DomainEvent.php';
require_once __DIR__ . '/../Models/GastoModel.php';

class GastoCreatedDomainEvent extends DomainEvent
{
    private GastoModel $gasto;

    public function __construct(GastoModel $gasto)
    {
        parent::__construct('gasto.created');
        $this->gasto = $gasto;
    }

    public function gasto(): GastoModel
    {
        return $this->gasto;
    }

    public function payload(): array
    {
        return $this->gasto->toArray();
    }
}
