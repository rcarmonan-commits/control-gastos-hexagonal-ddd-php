<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Domain/Events/DomainEvent.php';

interface DomainEventPublisherPort
{
    public function publish(DomainEvent $event): void;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recentEvents(int $limit = 20): array;
}
