<?php

declare(strict_types=1);

require_once __DIR__ . '/DomainEvent.php';
require_once __DIR__ . '/../Models/UserModel.php';

class UserCreatedDomainEvent extends DomainEvent
{
    private UserModel $user;

    public function __construct(UserModel $user)
    {
        parent::__construct('user.created');
        $this->user = $user;
    }

    public function user(): UserModel
    {
        return $this->user;
    }

    public function payload(): array
    {
        return array(
            'id' => $this->user->id()->value(),
            'name' => $this->user->name()->value(),
            'email' => $this->user->email()->value(),
            'role' => $this->user->role(),
            'status' => $this->user->status(),
        );
    }
}
