<?php

declare(strict_types=1);

require_once __DIR__ . '/../Ports/In/UpdateGastoUseCase.php';
require_once __DIR__ . '/../Ports/Out/GetGastoByIdPort.php';
require_once __DIR__ . '/../Ports/Out/UpdateGastoPort.php';
require_once __DIR__ . '/../Ports/Out/CachePort.php';
require_once __DIR__ . '/../Ports/Out/DomainEventPublisherPort.php';
require_once __DIR__ . '/Mappers/GastoApplicationMapper.php';
require_once __DIR__ . '/../../Domain/Exceptions/GastoNotFoundException.php';
require_once __DIR__ . '/../../Domain/Events/GastoUpdatedDomainEvent.php';

final class UpdateGastoService implements UpdateGastoUseCase
{
    private GetGastoByIdPort $getGastoByIdPort;
    private UpdateGastoPort $updateGastoPort;
    private ?CachePort $cachePort;
    private ?DomainEventPublisherPort $eventPublisher;

    public function __construct(
        GetGastoByIdPort $getGastoByIdPort,
        UpdateGastoPort $updateGastoPort,
        ?CachePort $cachePort = null,
        ?DomainEventPublisherPort $eventPublisher = null
    ) {
        $this->getGastoByIdPort = $getGastoByIdPort;
        $this->updateGastoPort = $updateGastoPort;
        $this->cachePort = $cachePort;
        $this->eventPublisher = $eventPublisher;
    }

    public function execute(UpdateGastoCommand $command): GastoModel
    {
        $gastoId = new GastoId($command->getId());
        $existing = $this->getGastoByIdPort->getById($gastoId);

        if ($existing === null) {
            throw GastoNotFoundException::becauseIdWasNotFound($gastoId->value());
        }

        $updatedModel = GastoApplicationMapper::fromUpdateCommandToModel($command);
        $persisted = $this->updateGastoPort->update($updatedModel);

        if ($this->cachePort !== null) {
            $this->cachePort->clearPrefix('gastos:');
        }

        if ($this->eventPublisher !== null) {
            $this->eventPublisher->publish(new GastoUpdatedDomainEvent($persisted));
        }

        return $persisted;
    }
}
