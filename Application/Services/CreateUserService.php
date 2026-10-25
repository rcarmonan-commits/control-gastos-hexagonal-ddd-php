<?php

declare(strict_types=1);

require_once __DIR__ . '/../Ports/In/CreateUserUseCase.php';
require_once __DIR__ . '/../Ports/Out/SaveUserPort.php';
require_once __DIR__ . '/../Ports/Out/GetUserByEmailPort.php';
require_once __DIR__ . '/../Ports/Out/GetUserByIdPort.php';
require_once __DIR__ . '/../Ports/Out/CachePort.php';
require_once __DIR__ . '/../Ports/Out/DomainEventPublisherPort.php';
require_once __DIR__ . '/Mappers/UserApplicationMapper.php';
require_once __DIR__ . '/../../Domain/Exceptions/UserAlreadyExistsException.php';
require_once __DIR__ . '/../../Domain/Events/UserCreatedDomainEvent.php';

final class CreateUserService implements CreateUserUseCase
{
    private SaveUserPort $saveUserPort;
    private GetUserByEmailPort $getUserByEmailPort;
    private GetUserByIdPort $getUserByIdPort;
    private ?CachePort $cachePort;
    private ?DomainEventPublisherPort $eventPublisher;

    public function __construct(
        SaveUserPort $saveUserPort,
        GetUserByEmailPort $getUserByEmailPort,
        GetUserByIdPort $getUserByIdPort,
        ?CachePort $cachePort = null,
        ?DomainEventPublisherPort $eventPublisher = null
    ) {
        $this->saveUserPort = $saveUserPort;
        $this->getUserByEmailPort = $getUserByEmailPort;
        $this->getUserByIdPort = $getUserByIdPort;
        $this->cachePort = $cachePort;
        $this->eventPublisher = $eventPublisher;
    }

    public function execute(CreateUserCommand $command): UserModel
    {
        $emailVo = new UserEmail($command->getEmail());
        if ($this->getUserByEmailPort->getByEmail($emailVo) !== null) {
            throw UserAlreadyExistsException::becauseEmailAlreadyExists($emailVo->value());
        }

        if ($command->getId() !== '') {
            $idVo = new UserId($command->getId());
            if ($this->getUserByIdPort->getById($idVo) !== null) {
                throw UserAlreadyExistsException::becauseIdAlreadyExists($idVo->value());
            }
        }

        $userModel = UserApplicationMapper::fromCreateCommandToModel($command);
        $saved = $this->saveUserPort->save($userModel);

        if ($this->cachePort !== null) {
            $this->cachePort->clearPrefix('users:');
        }

        if ($this->eventPublisher !== null) {
            $this->eventPublisher->publish(new UserCreatedDomainEvent($saved));
        }

        return $saved;
    }
}
