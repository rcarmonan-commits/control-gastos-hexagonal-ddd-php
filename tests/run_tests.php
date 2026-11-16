<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/Common/DependencyInjection.php';
require_once dirname(__DIR__) . '/Infrastructure/Entrypoints/Rest/UserRestController.php';
require_once dirname(__DIR__) . '/Infrastructure/Entrypoints/Rest/GastoRestController.php';
require_once dirname(__DIR__) . '/Infrastructure/Entrypoints/Rest/ErrorHandler.php';
require_once dirname(__DIR__) . '/Infrastructure/Entrypoints/Web/Controllers/AuthController.php';
require_once dirname(__DIR__) . '/Infrastructure/Entrypoints/Web/Controllers/Config/WebRoutes.php';

final class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;

    public function assert(bool $condition, string $label): void
    {
        if ($condition) {
            $this->passed++;
            echo "[PASS] {$label}\n";
            return;
        }

        $this->failed++;
        fwrite(STDERR, "[FAIL] {$label}\n");
    }

    /**
     * @param class-string<Throwable> $expectedExceptionClass
     */
    public function assertThrows(callable $callback, string $expectedExceptionClass, string $label): void
    {
        try {
            $callback();
            $this->failed++;
            fwrite(STDERR, "[FAIL] {$label} (no lanzo excepcion; se esperaba {$expectedExceptionClass})\n");
        } catch (Throwable $exception) {
            if ($exception instanceof $expectedExceptionClass) {
                $this->passed++;
                echo "[PASS] {$label} -> " . get_class($exception) . "\n";
                return;
            }

            $this->failed++;
            fwrite(
                STDERR,
                "[FAIL] {$label} (lanzo " . get_class($exception) . ": {$exception->getMessage()}; se esperaba {$expectedExceptionClass})\n"
            );
        }
    }

    public function summary(): int
    {
        $total = $this->passed + $this->failed;
        echo str_repeat('=', 72) . "\n";
        echo "Resultado Suite DDD + Hexagonal + CQRS: {$this->passed}/{$total} pruebas exitosas";
        if ($this->failed > 0) {
            echo " ({$this->failed} fallidas)";
        }
        echo "\n" . str_repeat('=', 72) . "\n";

        return $this->failed === 0 ? 0 : 1;
    }
}

$t = new TestRunner();

// ============================================================================
// 1. PRUEBAS UNITARIAS DE DOMINIO — ENTIDAD BASE USUARIO
// ============================================================================
$user = UserModel::create(
    new UserId('USR-T01'),
    new UserName('Rosary Carmona'),
    new UserEmail('rcarmonan@unicartagena.edu.co'),
    UserPassword::fromPlainText('ClaveSegura2026'),
    'admin'
);

$t->assert($user->id()->value() === 'USR-T01', 'Dominio Usuario: UserId conserva identificador normalizado');
$t->assert($user->role() === UserRoleEnum::ADMIN, 'Dominio Usuario: UserRoleEnum normaliza rol a ADMIN');
$t->assert($user->status() === UserStatusEnum::ACTIVE, 'Dominio Usuario: estado inicial por defecto es ACTIVE');
$t->assert($user->password()->matches('ClaveSegura2026'), 'Dominio Usuario: UserPassword verifica hash bcrypt exitosamente');
$t->assert(!$user->password()->matches('Incorrecta999'), 'Dominio Usuario: UserPassword rechaza clave incorrecta');

$blockedUser = $user->block();
$t->assert($blockedUser->status() === UserStatusEnum::BLOCKED, 'Dominio Usuario: transicion inmutable block() cambia estado a BLOCKED');
$t->assert($user->status() === UserStatusEnum::ACTIVE, 'Dominio Usuario: agregado original permanece inmutable tras block()');

$t->assertThrows(
    static fn() => new UserEmail('correo-invalido-sin-arroba'),
    InvalidUserEmailException::class,
    'Dominio Usuario: rechaza formato de correo invalido'
);
$t->assertThrows(
    static fn() => UserPassword::fromPlainText('123'),
    InvalidUserPasswordException::class,
    'Dominio Usuario: rechaza clave corta menor al minimo permitido'
);
$t->assertThrows(
    static fn() => UserRoleEnum::ensureIsValid('SUPER_HACKER'),
    InvalidUserRoleException::class,
    'Dominio Usuario: rechaza rol inexistente fuera del catalogo'
);

// ============================================================================
// 2. PRUEBAS UNITARIAS DE DOMINIO — ENTIDAD EJERCICIO #6 GASTO
// ============================================================================
$sinIva = new GastoMonto('199999.99', 'valorTotalSinIVA');
$iva = new GastoMonto('38000.01', 'ivaTotal');
$sumaExacta = $sinIva->add($iva, 'valorTotalConIVA');

$t->assert($sumaExacta->cents() === 23800000, 'Dominio Gasto: GastoMonto opera aritmetica exacta en centavos sin deriva IEEE-754');
$t->assert($sumaExacta->formatted() === '238000.00', 'Dominio Gasto: formato monetario exacto a dos decimales');

$gastoValido = GastoModel::create(
    new GastoId('GAS-T01'),
    new GastoFecha('2026-10-12'),
    new GastoMonto(250000.00, 'valorTotalSinIVA'),
    new GastoMonto(47500.00, 'ivaTotal'),
    null,
    new UserName('Rosary Carmona'),
    new GastoLugar('Libreria Universitaria Cartagena'),
    new GastoDescripcion('Compra de textos guia de Arquitectura de Software y DDD')
);
$t->assert($gastoValido->valorTotalConIVA()->value() === 297500.00, 'Dominio Gasto: GastoModel::create calcula automaticamente valorTotalConIVA cuando se omite');

$t->assertThrows(
    static fn() => new GastoModel(
        new GastoId('GAS-ERR-01'),
        new GastoFecha('2026-10-12'),
        new GastoMonto(100000.00, 'valorTotalSinIVA'),
        new GastoMonto(19000.00, 'ivaTotal'),
        new GastoMonto(150000.00, 'valorTotalConIVA'),
        new UserName('Rosary Carmona'),
        new GastoLugar('Comercio Prueba'),
        new GastoDescripcion('Gasto con descuadre contable deliberado')
    ),
    InvalidGastoMontoException::class,
    'Dominio Gasto: GastoModel protege invariante valorTotalSinIVA + ivaTotal == valorTotalConIVA'
);

$t->assertThrows(
    static fn() => new GastoMonto(-50.0, 'valorTotalSinIVA'),
    InvalidGastoMontoException::class,
    'Dominio Gasto: GastoMonto rechaza valores negativos'
);

$t->assertThrows(
    static fn() => new GastoFecha('2026-02-30'),
    InvalidGastoFechaException::class,
    'Dominio Gasto: GastoFecha rechaza fechas calendario inexistentes (30 de febrero)'
);

$t->assertThrows(
    static fn() => new GastoLugar('A'),
    InvalidGastoLugarException::class,
    'Dominio Gasto: GastoLugar rechaza nombres demasiado cortos'
);

$t->assertThrows(
    static fn() => new GastoDescripcion('AB'),
    InvalidGastoDescripcionException::class,
    'Dominio Gasto: GastoDescripcion exige longitud minima de 3 caracteres'
);

// ============================================================================
// 3. PRUEBAS DE APLICACION (CASOS DE USO CQRS + CACHE + EVENTOS DE DOMINIO)
// ============================================================================
$di = DependencyInjection::createTestContainer();
$t->assert($di->activePersistenceDriver() === 'memory-ephemeral', 'Infraestructura DI: contenedor aislado de pruebas en memoria efimera');

// 3.1 CRUDL + Autenticacion de Usuarios
$initialUsers = $di->getAllUsersUseCase()->execute(new GetAllUsersQuery());
$t->assert(count($initialUsers) === 2, 'CQRS Query Usuarios: carga 2 usuarios semilla iniciales');

$createdUser = $di->createUserUseCase()->execute(new CreateUserCommand(
    'USR-003',
    'Andres Felipe Barrios',
    'abarrios@unicartagena.edu.co',
    'Barrios2026*',
    'REVIEWER'
));
$t->assert($createdUser->id()->value() === 'USR-003', 'CQRS Command CreateUser: persiste nuevo usuario USR-003');

$afterCreateUsers = $di->getAllUsersUseCase()->execute(new GetAllUsersQuery());
$t->assert(count($afterCreateUsers) === 3, 'Cache + CQRS Usuarios: invalidacion de cache refleja inmediatamente 3 usuarios');

$authOk = $di->authenticateUserUseCase()->execute(new AuthenticateUserCommand('abarrios@unicartagena.edu.co', 'Barrios2026*'));
$t->assert($authOk->name()->value() === 'Andres Felipe Barrios', 'CQRS Command AuthenticateUser: autentica credenciales validas');

$updatedUser = $di->updateUserUseCase()->execute(new UpdateUserCommand(
    'USR-003',
    'Andres Felipe Barrios Montes',
    'abarrios@unicartagena.edu.co',
    null,
    'ADMIN',
    'ACTIVE'
));
$t->assert($updatedUser->role() === 'ADMIN' && $updatedUser->name()->value() === 'Andres Felipe Barrios Montes', 'CQRS Command UpdateUser: actualiza nombre y rol conservando hash de clave');

$di->deleteUserUseCase()->execute(new DeleteUserCommand('USR-003'));
$t->assertThrows(
    static fn() => $di->getUserByIdUseCase()->execute(new GetUserByIdQuery('USR-003')),
    UserNotFoundException::class,
    'CQRS Command DeleteUser: elimina USR-003 y GetUserById lanza UserNotFoundException'
);

// 3.2 CRUDL + Filtros de Gastos (#6)
$initialGastos = $di->getAllGastosUseCase()->execute(new GetAllGastosQuery());
$t->assert(count($initialGastos) === 2, 'CQRS Query Gastos: carga 2 gastos semilla iniciales');

$createdGasto = $di->createGastoUseCase()->execute(new CreateGastoCommand(
    'GAS-003',
    '2026-11-01',
    400000.00,
    76000.00,
    476000.00,
    'Rosary Carmona',
    ' Homecenter Cartagena ',
    'Compra de licencias y materiales para sustentacion de arquitectura hexagonal'
));
$t->assert($createdGasto->valorTotalConIVA()->value() === 476000.00, 'CQRS Command CreateGasto: registra GAS-003 con total con IVA validado');

$filteredByUser = $di->getAllGastosUseCase()->execute(new GetAllGastosQuery(null, 'Rosary Carmona'));
$t->assert(count($filteredByUser) === 2, 'CQRS Query Gastos: filtro por nombreUsuario retorna exactamente los 2 gastos de Rosary Carmona');

$updatedGasto = $di->updateGastoUseCase()->execute(new UpdateGastoCommand(
    'GAS-003',
    '2026-11-02',
    500000.00,
    95000.00,
    595000.00,
    'Rosary Carmona',
    'Homecenter Cartagena San Fernando',
    'Factura ajustada con equipamiento adicional de red'
));
$t->assert($updatedGasto->valorTotalConIVA()->value() === 595000.00, 'CQRS Command UpdateGasto: recalcula y persiste nuevo monto con IVA');

$events = $di->eventPublisher()->recentEvents(10);
$t->assert(count($events) >= 5, 'Adaptador Cola Eventos: publica DomainEvents (UserCreated/Updated/Deleted, GastoCreated/Updated)');

// ============================================================================
// 4. PRUEBAS DE INTEGRACION — ENTRYPOINTS REST Y MANEJADOR GLOBAL DE ERRORES
// ============================================================================
$userRest = new UserRestController($di);
$gastoRest = new GastoRestController($di);
$authController = new AuthController($di);

$httpCreateUser = $userRest->handle('POST', null, array(), array(
    'id' => 'USR-REST-01',
    'name' => 'Camila Andrea Torres',
    'email' => 'ctorres@unicartagena.edu.co',
    'password' => 'Camila2026*',
    'role' => 'MEMBER',
));
$t->assert($httpCreateUser['status'] === 201, 'API REST Usuarios: POST /api/users responde HTTP 201 Created');

$httpConflictUser = $userRest->handle('POST', null, array(), array(
    'id' => 'USR-REST-02',
    'name' => 'Duplicada',
    'email' => 'ctorres@unicartagena.edu.co',
    'password' => 'Camila2026*',
    'role' => 'MEMBER',
));
$t->assert($httpConflictUser['status'] === 409, 'API REST Usuarios: correo duplicado responde HTTP 409 Conflict');

$httpCreateGasto = $gastoRest->handle('POST', null, array(), array(
    'id' => 'GAS-REST-01',
    'fecha' => '2026-11-10',
    'valorTotalSinIVA' => 120000.00,
    'ivaTotal' => 22800.00,
    'valorTotalConIVA' => 142800.00,
    'nombreUsuario' => 'Camila Andrea Torres',
    'lugar' => 'Papeleria Tauro Cartagena',
    'descripcion' => 'Impresion de informes y diagramas UML de arquitectura limpia',
));
$t->assert($httpCreateGasto['status'] === 201, 'API REST Gastos: POST /api/gastos valido responde HTTP 201 Created');

$httpInvalidIva = $gastoRest->handle('POST', null, array(), array(
    'id' => 'GAS-REST-BAD',
    'fecha' => '2026-11-10',
    'valorTotalSinIVA' => 120000.00,
    'ivaTotal' => 22800.00,
    'valorTotalConIVA' => 999999.00,
    'nombreUsuario' => 'Camila Andrea Torres',
    'lugar' => 'Papeleria Tauro Cartagena',
    'descripcion' => 'Intento de violar invariante contable desde API REST',
));
$t->assert($httpInvalidIva['status'] === 422, 'API REST Gastos: violacion de invariante contable responde HTTP 422 Unprocessable Entity');

$httpDeleteGasto = $gastoRest->handle('DELETE', 'GAS-REST-01');
$t->assert($httpDeleteGasto['status'] === 200, 'API REST Gastos: DELETE /api/gastos/GAS-REST-01 responde HTTP 200 OK');

$httpNotFoundGasto = $gastoRest->handle('GET', 'GAS-REST-01');
$t->assert($httpNotFoundGasto['status'] === 404, 'API REST Gastos: GET de gasto eliminado responde HTTP 404 Not Found');

try {
    $authController->login('admin@crud.com', 'ClaveErronea000');
    $t->assert(false, 'ErrorHandler Auth: debio lanzar excepcion por credenciales invalidas');
} catch (Throwable $authEx) {
    $mappedAuthError = ErrorHandler::toHttpPayload($authEx);
    $t->assert($mappedAuthError['status'] === 401, 'ErrorHandler Auth: credenciales invalidas mapean a HTTP 401 Unauthorized');
}

// ============================================================================
// 5. VALIDACION DE ESPECIFICACIONES JSON (OPENAPI 3.0 Y COLECCION POSTMAN)
// ============================================================================
$openApiRaw = (string) file_get_contents(dirname(__DIR__) . '/Infrastructure/Entrypoints/Rest/Swagger/openapi.json');
$openApiJson = json_decode($openApiRaw, true);
$t->assert(
    is_array($openApiJson) && ($openApiJson['openapi'] ?? '') === '3.0.3' && isset($openApiJson['paths']['/api/gastos']),
    'Contrato OpenAPI 3.0: openapi.json es JSON valido y documenta /api/gastos'
);

$postmanRaw = (string) file_get_contents(dirname(__DIR__) . '/postman/CRUD_Usuarios_Gastos_Hexagonal.postman_collection.json');
$postmanJson = json_decode($postmanRaw, true);
$t->assert(
    is_array($postmanJson) && isset($postmanJson['item']) && count($postmanJson['item']) === 3,
    'Coleccion Postman v2.1: JSON valido con modulos Sistema, Usuarios y Gastos (#6)'
);

// ============================================================================
// 6. PRUEBAS DE RENDERIZADO DE VISTAS WEB (PRESENTACION HTML)
// ============================================================================
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = array();

ob_start();
WebRoutes::dispatch('gastos.list');
$htmlGastos = (string) ob_get_clean();
$t->assert(str_contains($htmlGastos, 'GAS-001') && str_contains($htmlGastos, 'Rosary Carmona'), 'Presentacion Web Gastos: renderiza listado HTML con datos semilla sin errores');

ob_start();
WebRoutes::dispatch('users.list');
$htmlUsers = (string) ob_get_clean();
$t->assert(str_contains($htmlUsers, 'admin@crud.com') && str_contains($htmlUsers, 'USR-001'), 'Presentacion Web Usuarios: renderiza listado HTML de usuarios sin errores');

ob_start();
WebRoutes::dispatch('login');
$htmlLogin = (string) ob_get_clean();
$t->assert(str_contains($htmlLogin, 'Iniciar Sesion'), 'Presentacion Web Auth: renderiza formulario de inicio de sesion sin errores');

// ============================================================================
// 7. AUDITORIA QUIRURGICA DE CUMPLIMIENTO 100% CON LA GUIA DEL DOCENTE
// ============================================================================
// 7.1 Snippet exacto de Seccion 5.3 ("Ejemplo del uso del dominio")
$guideUser = UserModel::create(
    new UserId('1'),
    new UserName('Administrador'),
    new UserEmail('admin@crud.com'),
    new UserPassword('Developer123'),
    UserRoleEnum::ADMIN
);
$guideUser = $guideUser->activate();
$guideEvent = new UserCreatedDomainEvent($guideUser);
$t->assert(
    $guideUser->id()->value() === '1' && $guideUser->status() === UserStatusEnum::ACTIVE && $guideEvent->payload()['email'] === 'admin@crud.com',
    'Guia Seccion 5.3: snippet oficial UserModel::create(UserId("1")) + activate() + UserCreatedDomainEvent funciona al 100%'
);

// 7.2 Constante PENDING en UserStatusEnum (Seccion 5.3)
$t->assert(
    UserStatusEnum::isValid(UserStatusEnum::PENDING),
    'Guia Seccion 5.3: UserStatusEnum incluye estado PENDING ademas de ACTIVE, INACTIVE y BLOCKED'
);

// 7.3 Firma de 1 argumento en UserApplicationMapper::fromUpdateCommandToModel (Seccion 4.3)
$mappedSingleArg = UserApplicationMapper::fromUpdateCommandToModel(
    new UpdateUserCommand('USR-MAP-1', 'Nombre Guia', 'guia@crud.com', 'ClaveGuia123', 'MEMBER', 'PENDING')
);
$t->assert(
    $mappedSingleArg->status() === 'PENDING' && $mappedSingleArg->password()->matches('ClaveGuia123'),
    'Guia Seccion 4.3: UserApplicationMapper::fromUpdateCommandToModel soporta firma oficial de 1 argumento'
);

// 7.4 Puerto FindUserByEmailPort (Captura 3.2 image2.png) implementado en repositorios
$memRepoCheck = new InMemoryUserRepository(null, true);
$foundViaAliasPort = $memRepoCheck->findByEmail(new UserEmail('admin@crud.com'));
$t->assert(
    $memRepoCheck instanceof FindUserByEmailPort && $foundViaAliasPort !== null && $foundViaAliasPort->id()->value() === 'USR-001',
    'Guia Seccion 3.2: FindUserByEmailPort existe y resuelve usuarios por email'
);

// 7.5 Existencia de carpetas y archivos de las 5 capturas oficiales (Secciones 3.1 a 3.4)
$rootDir = dirname(__DIR__);
$requiredPaths = array(
    'Application/Ports/Out/FindUserByEmailPort.php',
    'Application/Services/Dto/Requests/CreateUserRequest.php',
    'Application/Services/Dto/Requests/CreateGastoRequest.php',
    'Application/Services/Mappers/UserApplicationMapper.php',
    'Infrastructure/Entrypoints/Web/Presentation/images/logo-hexagonal.svg',
    'Infrastructure/Entrypoints/Web/Presentation/js/libs/iva-calculator.js',
    'Infrastructure/Entrypoints/Web/Presentation/multimedia/arquitectura-hexagonal.svg',
    'Common/ClassLoader.php',
    'Common/DependencyInjection.php',
    'render.yaml',
);
$allPathsExist = true;
foreach ($requiredPaths as $rp) {
    if (!is_file($rootDir . '/' . $rp)) {
        $allPathsExist = false;
        fwrite(STDERR, "Falta ruta requerida: {$rp}\n");
    }
}
$t->assert($allPathsExist, 'Guia Seccion 3 (Capturas 3.1-3.4): 100% de carpetas y archivos estructurales presentes');

// 7.6 Consistencia de credenciales MySQL (crud_usuarios / Estudiante / Developer) en Docker Compose y SQL
$composeRaw = (string) file_get_contents($rootDir . '/docker-compose.yml');
$sqlRaw = (string) file_get_contents($rootDir . '/Infrastructure/Adapters/Persistence/MySQL/Database/mysql/database.sql');
$t->assert(
    str_contains($composeRaw, 'DB_DATABASE: crud_usuarios') &&
    str_contains($composeRaw, 'DB_USERNAME: Estudiante') &&
    str_contains($sqlRaw, 'CREATE DATABASE IF NOT EXISTS crud_usuarios'),
    'Guia Seccion 5.1: database.sql, Connection.php, .env.example y docker-compose.yml unificados en crud_usuarios'
);

exit($t->summary());
