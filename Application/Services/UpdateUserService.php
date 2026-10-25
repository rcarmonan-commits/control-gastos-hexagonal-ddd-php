<?php

declare(strict_types=1);

require_once __DIR__ . '/../Ports/In/UpdateUserUseCase.php';
require_once __DIR__ . '/../Ports/Out/GetUserByIdPort.php';
require_once __DIR__ . '/../Ports/Out/GetUserByEmailPort.php';
require_once __DIR__ . '/../Ports/Out/UpdateUserPort.php';
require_once __DIR__ . '/../Ports/Out/CachePort.php';
require_once __DIR__ . '/../Ports/Out/DomainEventPublisherPort.php';
require_once __DIR__ . '/Mappers/UserApplicationMapper.php';
require_once __DIR__ . '/../../Domain/Exceptions/UserNotFoundException.php';
require_once __DIR__ . '/../../Domain/Exceptions/UserAlreadyExistsException.php';
require_once __DIR__ . '/../../Domain/Events/UserUpdatedDomainEvent.php';

final class UpdateUserService implements UpdateUserUseCase
{
    private GetUserByIdPort $getUserByIdPort;
    private GetUserByEmailPort $getUserByEmailPort;
    private UpdateUserPort $updateUserPort;
    private ?CachePort $cachePort;
    private ?DomainEventPublisherPort $eventPublisher;

    public function __construct(
        GetUserByIdPort $getUserByIdPort,
        GetUserByEmailPort $getUserByEmailPort,
        UpdateUserPort $updateUserPort,
        ?CachePort $cachePort = null,
        ?DomainEventPublisherPort $eventPublisher = null
    ) {
        $this->getUserByIdPort = $getUserByIdPort;
        $this->getUserByEmailPort = $getUserByEmailPort;
        $this->updateUserPort = $updateUserPort;
        $this->cachePort = $cachePort;
        $this->eventPublisher = $eventPublisher;
    }

    public function execute(UpdateUserCommand $command): UserModel
    {
        $userId = new UserId($command->getId());
        $existingUser = $this->getUserByIdPort->getById($userId);

        if ($existingUser === null) {
            throw UserNotFoundException::becauseIdWasNotFound($userId->value());
        }

        $newEmail = new UserEmail($command->getEmail());
        $ownerOfEmail = $this->getUserByEmailPort->getByEmail($newEmail);
        if ($ownerOfEmail !== null && !$ownerOfEmail->id()->equals($userId)) {
            throw UserAlreadyExistsException::becauseEmailAlreadyExists($newEmail->value());
        }

        $updatedModel = UserApplicationMapper::fromUpdateCommandToModel($command, $existingUser->password());
        $persisted = $this->updateUserPort->update($updatedModel);

        if ($this->cachePort !== null) {
            $this->cachePort->clearPrefix('users:');
        }

        if ($this->eventPublisher !== null) {
            $this->eventPublisher->publish(new UserUpdatedDomainEvent($persisted));
        }

        return $persisted;
    }
}
