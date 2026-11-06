<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Application/Ports/Out/DomainEventPublisherPort.php';

final class FileQueueEventPublisherAdapter implements DomainEventPublisherPort
{
    private string $queueFile;

    /** @var array<int, array<string, mixed>> */
    private array $publishedInMemory = array();

    public function __construct(?string $queueFile = null)
    {
        $dir = dirname(__DIR__, 3) . '/storage/queue';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $this->queueFile = $queueFile ?? ($dir . '/domain_events.jsonl');
    }

    public function publish(DomainEvent $event): void
    {
        $envelope = array(
            'eventId' => 'EVT-' . strtoupper(bin2hex(random_bytes(4))),
            'eventName' => $event->eventName(),
            'occurredOn' => $event->occurredOn(),
            'payload' => $event->payload(),
        );

        $this->publishedInMemory[] = $envelope;

        $jsonLine = json_encode($envelope, JSON_UNESCAPED_UNICODE);
        if ($jsonLine !== false) {
            @file_put_contents($this->queueFile, $jsonLine . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }

    public function recentEvents(int $limit = 20): array
    {
        if (!is_file($this->queueFile)) {
            return array_slice(array_reverse($this->publishedInMemory), 0, $limit);
        }

        $lines = @file($this->queueFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return array_slice(array_reverse($this->publishedInMemory), 0, $limit);
        }

        $parsed = array();
        foreach (array_slice(array_reverse($lines), 0, $limit) as $line) {
            $decoded = json_decode($line, true);
            if (is_array($decoded)) {
                $parsed[] = $decoded;
            }
        }

        return $parsed;
    }
}
