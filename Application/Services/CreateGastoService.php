<?php

declare(strict_types=1);

require_once __DIR__ . '/../Ports/In/CreateGastoUseCase.php';
require_once __DIR__ . '/../Ports/Out/SaveGastoPort.php';
require_once __DIR__ . '/../Ports/Out/GetGastoByIdPort.php';
require_once __DIR__ . '/../Ports/Out/CachePort.php';
require_once __DIR__ . '/../Ports/Out/DomainEventPublisherPort.php';
require_once __DIR__ . '/Mappers/GastoApplicationMapper.php';
require_once __DIR__ . '/../../Domain/Events/GastoCreatedDomainEvent.php';

final class CreateGastoService implements CreateGastoUseCase
{
    private SaveGastoPort $saveGastoPort;
    private GetGastoByIdPort $getGastoByIdPort;
    private ?CachePort $cachePort;
    private ?DomainEventPublisherPort $eventPublisher;

    public function __construct(
        SaveGastoPort $saveGastoPort,
        GetGastoByIdPort $getGastoByIdPort,
        ?CachePort $cachePort = null,
        ?DomainEventPublisherPort $eventPublisher = null
    ) {
        $this->saveGastoPort = $saveGastoPort;
        $this->getGastoByIdPort = $getGastoByIdPort;
        $this->cachePort = $cachePort;
        $this->eventPublisher = $eventPublisher;
    }

    public function execute(CreateGastoCommand $command): GastoModel
    {
        if ($command->getId() !== '') {
            $existing = $this->getGastoByIdPort->getById(new GastoId($command->getId()));
            if ($existing !== null) {
                throw new DomainException('Ya existe un gasto registrado con el identificador: ' . $command->getId());
            }
        }

        $gastoModel = GastoApplicationMapper::fromCreateCommandToModel($command);
        $saved = $this->saveGastoPort->save($gastoModel);

        if ($this->cachePort !== null) {
            $this->cachePort->clearPrefix('gastos:');
        }

        if ($this->eventPublisher !== null) {
            $this->eventPublisher->publish(new GastoCreatedDomainEvent($saved));
        }

        return $saved;
    }
}
