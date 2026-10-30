<?php

declare(strict_types=1);

require_once __DIR__ . '/../Ports/In/DeleteUserUseCase.php';
require_once __DIR__ . '/../Ports/Out/GetUserByIdPort.php';
require_once __DIR__ . '/../Ports/Out/DeleteUserPort.php';
require_once __DIR__ . '/../Ports/Out/CachePort.php';
require_once __DIR__ . '/../Ports/Out/DomainEventPublisherPort.php';
require_once __DIR__ . '/Mappers/UserApplicationMapper.php';
require_once __DIR__ . '/../../Domain/Exceptions/UserNotFoundException.php';
require_once __DIR__ . '/../../Domain/Events/UserDeletedDomainEvent.php';

final class DeleteUserService implements DeleteUserUseCase
{
    private GetUserByIdPort $getUserByIdPort;
    private DeleteUserPort $deleteUserPort;
    private ?CachePort $cachePort;
    private ?DomainEventPublisherPort $eventPublisher;

    public function __construct(
        GetUserByIdPort $getUserByIdPort,
        DeleteUserPort $deleteUserPort,
        ?CachePort $cachePort = null,
        ?DomainEventPublisherPort $eventPublisher = null
    ) {
        $this->getUserByIdPort = $getUserByIdPort;
        $this->deleteUserPort = $deleteUserPort;
        $this->cachePort = $cachePort;
        $this->eventPublisher = $eventPublisher;
    }

    public function execute(DeleteUserCommand $command): void
    {
        $userId = UserApplicationMapper::fromDeleteCommandToUserId($command);
        $existingUser = $this->getUserByIdPort->getById($userId);

        if ($existingUser === null) {
            throw UserNotFoundException::becauseIdWasNotFound($userId->value());
        }

        $this->deleteUserPort->delete($userId);

        if ($this->cachePort !== null) {
            $this->cachePort->clearPrefix('users:');
        }

        if ($this->eventPublisher !== null) {
            $this->eventPublisher->publish(new UserDeletedDomainEvent($userId));
        }
    }
}
