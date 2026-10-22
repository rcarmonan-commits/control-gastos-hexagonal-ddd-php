<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Services/Dto/Commands/AuthenticateUserCommand.php';
require_once __DIR__ . '/../../../Domain/Models/UserModel.php';

interface AuthenticateUserUseCase
{
    public function execute(AuthenticateUserCommand $command): UserModel;
}
