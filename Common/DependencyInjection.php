<?php

declare(strict_types=1);

require_once __DIR__ . '/EnvLoader.php';
require_once __DIR__ . '/ClassLoader.php';

ClassLoader::register();

final class DependencyInjection
{
    private static ?self $container = null;

    private SaveUserPort&GetUserByIdPort&GetUserByEmailPort&GetAllUsersPort&UpdateUserPort&DeleteUserPort $userRepository;
    private SaveGastoPort&GetGastoByIdPort&GetAllGastosPort&UpdateGastoPort&DeleteGastoPort $gastoRepository;
    private CachePort $cacheAdapter;
    private DomainEventPublisherPort $eventPublisher;
    private TaxReferenceRestClient $taxRestClient;
    private string $activePersistenceDriver;

    public function __construct(?string $forceDriver = null, bool $pureInMemoryForTests = false)
    {
        EnvLoader::load();

        $this->cacheAdapter = new FileCacheAdapter();
        $this->eventPublisher = new FileQueueEventPublisherAdapter();
        $this->taxRestClient = new TaxReferenceRestClient();

        $configuredDriver = strtolower(trim($forceDriver ?? (EnvLoader::get('PERSISTENCE_DRIVER', 'mysql') ?? 'mysql')));

        if ($configuredDriver === 'memory') {
            $dataDir = dirname(__DIR__) . '/storage/data';
            $this->userRepository = new InMemoryUserRepository(
                $pureInMemoryForTests ? null : ($dataDir . '/users.json'),
                true
            );
            $this->gastoRepository = new InMemoryGastoRepository(
                $pureInMemoryForTests ? null : ($dataDir . '/gastos.json'),
                true
            );
            $this->activePersistenceDriver = $pureInMemoryForTests ? 'memory-ephemeral' : 'memory-json';
            return;
        }

        try {
            if (!extension_loaded('pdo_mysql')) {
                throw new RuntimeException('La extension pdo_mysql no esta cargada en este runtime PHP.');
            }
            $pdo = Connection::getInstance();
            $this->userRepository = new UserRepositoryMySQL($pdo);
            $this->gastoRepository = new GastoRepositoryMySQL($pdo);
            $this->activePersistenceDriver = 'mysql';
        } catch (Throwable $exception) {
            $dataDir = dirname(__DIR__) . '/storage/data';
            $this->userRepository = new InMemoryUserRepository($dataDir . '/users.json', true);
            $this->gastoRepository = new InMemoryGastoRepository($dataDir . '/gastos.json', true);
            $this->activePersistenceDriver = 'memory-fallback';
        }
    }

    public static function getInstance(): self
    {
        if (self::$container === null) {
            self::$container = new self();
        }

        return self::$container;
    }

    public static function createTestContainer(): self
    {
        return new self('memory', true);
    }

    public function activePersistenceDriver(): string
    {
        return $this->activePersistenceDriver;
    }

    public function createUserUseCase(): CreateUserUseCase
    {
        return new CreateUserService(
            $this->userRepository,
            $this->userRepository,
            $this->userRepository,
            $this->cacheAdapter,
            $this->eventPublisher
        );
    }

    public function updateUserUseCase(): UpdateUserUseCase
    {
        return new UpdateUserService(
            $this->userRepository,
            $this->userRepository,
            $this->userRepository,
            $this->cacheAdapter,
            $this->eventPublisher
        );
    }

    public function deleteUserUseCase(): DeleteUserUseCase
    {
        return new DeleteUserService(
            $this->userRepository,
            $this->userRepository,
            $this->cacheAdapter,
            $this->eventPublisher
        );
    }

    public function getUserByIdUseCase(): GetUserByIdUseCase
    {
        return new GetUserByIdService($this->userRepository);
    }

    public function getAllUsersUseCase(): GetAllUsersUseCase
    {
        return new GetAllUsersService($this->userRepository, $this->cacheAdapter);
    }

    public function authenticateUserUseCase(): AuthenticateUserUseCase
    {
        return new AuthenticateUserService($this->userRepository);
    }

    public function createGastoUseCase(): CreateGastoUseCase
    {
        return new CreateGastoService(
            $this->gastoRepository,
            $this->gastoRepository,
            $this->cacheAdapter,
            $this->eventPublisher
        );
    }

    public function updateGastoUseCase(): UpdateGastoUseCase
    {
        return new UpdateGastoService(
            $this->gastoRepository,
            $this->gastoRepository,
            $this->cacheAdapter,
            $this->eventPublisher
        );
    }

    public function deleteGastoUseCase(): DeleteGastoUseCase
    {
        return new DeleteGastoService(
            $this->gastoRepository,
            $this->gastoRepository,
            $this->cacheAdapter,
            $this->eventPublisher
        );
    }

    public function getGastoByIdUseCase(): GetGastoByIdUseCase
    {
        return new GetGastoByIdService($this->gastoRepository);
    }

    public function getAllGastosUseCase(): GetAllGastosUseCase
    {
        return new GetAllGastosService($this->gastoRepository, $this->cacheAdapter);
    }

    public function eventPublisher(): DomainEventPublisherPort
    {
        return $this->eventPublisher;
    }

    public function taxRestClient(): TaxReferenceRestClient
    {
        return $this->taxRestClient;
    }
}
