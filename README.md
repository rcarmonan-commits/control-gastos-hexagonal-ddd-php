# Sistema CRUDL de Usuarios y Control de Gastos (Ejercicio #6) — Arquitectura Hexagonal, DDD y CQRS en PHP 8.3

> **Universidad de Cartagena** · Facultad de Ingeniería · Programa de Ingeniería de Sistemas  
> **Asignatura:** Desarrollo Web — Unidad 2 (Arquitecturas Limpias, Hexagonal, DDD y CQRS)  
> **Docente:** Ing. John Carlos Arrieta Arrieta  
> **Estudiante:** Rosary Eilyn Carmona Navarro (`rcarmonan@unicartagena.edu.co`)  
> **GitHub:** [`@rcarmonan-commits`](https://github.com/rcarmonan-commits)

---

## 1. Descripción General del Proyecto

Esta aplicación implementa de adentro hacia afuera (*Domain-First*) el flujo completo de persistencia **CRUDL** (*Create, Read, Update, Delete, List* + búsqueda, filtrado y autenticación) tanto para la entidad base de la guía académica (**`Usuario`**) como para la entidad asignada en el **Ejercicio #6 (`Gasto`)**, aplicando estrictamente:

- **Domain-Driven Design (DDD):** Modelos de dominio ricos (`UserModel`, `GastoModel`), Objetos de Valor (*Value Objects*) inmutables con autovalidación, Enumeraciones (`UserRoleEnum`, `UserStatusEnum`), Excepciones Semánticas de Dominio y Eventos de Dominio (`DomainEvent`).
- **Arquitectura Hexagonal (Ports & Adapters):** El núcleo (`Domain/` + `Application/`) no conoce detalles de MySQL, sesiones HTTP, vistas HTML ni JSON. Toda interacción ocurre mediante **Puertos de Entrada** (`Application/Ports/In/*UseCase.php`) y **Puertos de Salida** (`Application/Ports/Out/*Port.php`).
- **CQRS (Command Query Responsibility Segregation):** Separación explícita entre operaciones de escritura que mutan estado (**Commands**: `Create*Command`, `Update*Command`, `Delete*Command`, `AuthenticateUserCommand`) y operaciones de lectura optimizadas con caché (**Queries**: `GetAll*Query`, `Get*ByIdQuery`).
- **Principios SOLID y Clean Code:** Responsabilidad Única (un Servicio por Caso de Uso), Segregación de Interfaces (puertos granulares `SaveGastoPort`, `GetGastoByIdPort`, etc.) e Inversión de Dependencias gestionada en el *Composition Root* (`Common/DependencyInjection.php`).

---

## 2. Entidades del Dominio y Reglas de Negocio

### 2.1 Entidad Ejercicio #6: `Gasto` (`GastoModel`)
Modela un gasto corporativo o académico con todos los atributos exigidos en el enunciado:

| Atributo | Value Object | Reglas e Invariantes de Dominio |
| :--- | :--- | :--- |
| `id` | `GastoId` | Identificador único normalizado en mayúsculas (`GAS-XXX`), longitud entre 3 y 50 caracteres. |
| `fecha` | `GastoFecha` | Fecha calendario real validada con `checkdate()` en formato ISO `YYYY-MM-DD`. |
| `valorTotalSinIVA` | `GastoMonto` | Monto decimal no negativo operado internamente en **centavos enteros** (`int $cents`) para evitar errores de coma flotante IEEE-754. |
| `ivaTotal` | `GastoMonto` | Impuesto al valor agregado no negativo con precisión exacta de dos decimales. |
| `valorTotalConIVA` | `GastoMonto` | **Invariante contable estricto en `GastoModel`:** exige siempre que `valorTotalSinIVA + ivaTotal == valorTotalConIVA`. Si no coincide, lanza `InvalidGastoMontoException` (HTTP `422`). Si se omite al crear, el dominio lo calcula de forma exacta. |
| `nombreUsuario` | `UserName` | Nombre validado del usuario responsable del gasto (3 a 100 caracteres). |
| `lugar` | `GastoLugar` | Establecimiento o comercio donde se efectuó el gasto (2 a 150 caracteres). |
| `descripcion` | `GastoDescripcion` | Concepto detallado del gasto (mínimo 3 y máximo 500 caracteres). |

### 2.2 Entidad Base de la Guía: `Usuario` (`UserModel`)
- **Atributos y Value Objects:** `UserId`, `UserName`, `UserEmail` (validación RFC + normalización en minúsculas), `UserPassword` (hash seguro `PASSWORD_BCRYPT` con verificación `password_verify`), `RoleId`, `UserRoleEnum` (`ADMIN`, `MEMBER`, `REVIEWER`) y `UserStatusEnum` (`ACTIVE`, `INACTIVE`, `BLOCKED`).

---

## 3. Diagrama de Arquitectura Hexagonal + CQRS

```mermaid
flowchart LR
    subgraph Entrypoints["Adaptadores Primarios (Driving)"]
        WEB["Web MVC + Vistas PHP\n(Users & Gastos #6)"]
        REST["API RESTful JSON\n(/api/users, /api/gastos)"]
        SWAGGER["OpenAPI 3.0 / Swagger UI\n(/swagger)"]
    end

    subgraph Application["Capa de Aplicación (CQRS)"]
        IN["Puertos de Entrada\n(*UseCase)"]
        CMD["Commands\n(Create / Update / Delete / Auth)"]
        QRY["Queries\n(GetAll / GetById)"]
        SVC["Application Services\n+ Application Mappers"]
        OUT["Puertos de Salida\n(*Port)"]
    end

    subgraph Domain["Núcleo de Dominio (DDD)"]
        MOD["Agregados\nUserModel & GastoModel"]
        VO["Value Objects + Enums\n+ Excepciones + DomainEvents"]
    end

    subgraph Adapters["Adaptadores Secundarios (Driven)"]
        MYSQL["MySQL PDO Repositories\n(database.sql / Cloud DB)"]
        MEM["InMemory / JSON Fallback\n(Pruebas & Sin MySQL)"]
        CACHE["FileCacheAdapter\n(CachePort)"]
        QUEUE["FileQueueEventPublisher\n(DomainEventPublisherPort)"]
    end

    WEB --> IN
    REST --> IN
    SWAGGER -. documenta .-> REST
    CMD --> SVC
    QRY --> SVC
    IN <|.. SVC
    SVC --> MOD
    MOD --> VO
    SVC --> OUT
    OUT <|.. MYSQL
    OUT <|.. MEM
    OUT <|.. CACHE
    OUT <|.. QUEUE
```

---

## 4. Estructura de Carpetas del Proyecto

```text
6_Gasto/
├── Application/
│   ├── Exceptions/                     # Excepciones de orquestación
│   ├── Mappers/                        # UserApplicationMapper, GastoApplicationMapper
│   ├── Ports/
│   │   ├── In/                         # 11 Interfaces *UseCase (6 Usuario + 5 Gasto)
│   │   └── Out/                        # 13 Interfaces *Port (Persistencia, Caché y Eventos)
│   └── Services/
│       ├── Dto/
│       │   ├── Commands/               # DTOs de escritura CQRS
│       │   ├── Queries/                # DTOs de lectura CQRS
│       │   ├── Requests/               # Objetos Request DTO de aplicación
│       │   └── Responses/              # Respuestas desacopladas de aplicación
│       └── *Service.php                # 11 Casos de Uso implementados
├── Common/
│   ├── ClassLoader.php                 # Autoloader dinámico PSR-friendly sin dependencias externas
│   ├── DependencyInjection.php         # Contenedor IoC / Composition Root con fallback limpio
│   └── EnvLoader.php                   # Lector nativo de variables .env
├── Domain/
│   ├── Enums/                          # UserRoleEnum, UserStatusEnum
│   ├── Events/                         # DomainEvent y eventos Created/Updated/Deleted
│   ├── Exceptions/                     # 14 Excepciones semánticas de negocio
│   ├── Models/                         # UserModel, GastoModel
│   └── ValueObjects/                   # 10 Value Objects inmutables
├── Infrastructure/
│   ├── Adapters/
│   │   ├── Cache/                      # FileCacheAdapter
│   │   ├── FileStorage/                # FileQueueEventPublisherAdapter
│   │   ├── Persistence/
│   │   │   ├── InMemory/               # Repositorios en memoria / JSON semilla
│   │   │   └── MySQL/                  # Connection PDO, Entities, DTOs, Mappers, Repositories y SQL
│   │   └── Rest/                       # TaxReferenceRestClient (tarifa IVA de referencia)
│   └── Entrypoints/
│       ├── Rest/                       # UserRestController, GastoRestController, ErrorHandler, Swagger
│       └── Web/                        # Controladores Web, WebRoutes y Vistas HTML/CSS/JS
├── postman/                            # Colección Postman v2.1 lista para importar
├── public/                             # Front Controller HTTP (index.php + .htaccess)
├── tests/                              # Suite automatizada de pruebas (run_tests.php)
├── devops/ci-cd.yml                   # Pipeline CI/CD en GitHub Actions
├── Dockerfile                          # Imagen PHP 8.3 + Apache lista para producción/nube
└── docker-compose.yml                  # Orquestación App + MySQL 8.0 + phpMyAdmin
```

---

## 5. Ejecución Local, Con Docker y Despliegue Cloud

### Opción A: Ejecución inmediata con PHP 8.2+ / Laragon (con o sin MySQL)
1. Copiar `.env.example` a `.env` y ajustar credenciales MySQL si se dispone de servidor local (si MySQL o `pdo_mysql` no están activos, el contenedor IoC activa automáticamente el adaptador `InMemory` con datos semilla para poder probar toda la aplicación sin bloqueos).
2. Importar opcionalmente el esquema SQL en MySQL / MariaDB:
   ```bash
   mysql -u root -p < Infrastructure/Adapters/Persistence/MySQL/Database/mysql/database.sql
   ```
3. Iniciar el servidor embebido de PHP apuntando al Front Controller:
   ```bash
   php -S localhost:8080 -t public public/index.php
   ```
4. Abrir en el navegador:
   - **Módulo Web Gastos (#6):** `http://localhost:8080/?action=gastos.list`
   - **Módulo Web Usuarios:** `http://localhost:8080/?action=users.list`
   - **Login DDD:** `http://localhost:8080/?action=login` (`admin@crud.com` / `Developer123`)
   - **Visor Interactivo Swagger UI:** `http://localhost:8080/swagger`
   - **Healthcheck JSON:** `http://localhost:8080/api/health`

### Opción B: Entorno completo con Docker Compose (PHP 8.3 + MySQL 8.0 + phpMyAdmin)
```bash
docker compose up -d --build
```
- Aplicación Web y API REST: `http://localhost:8080`
- Swagger OpenAPI UI: `http://localhost:8080/swagger`
- phpMyAdmin: `http://localhost:8081` (`root` / `root_secret_2026`)

### Opción C: Base de Datos en la Nube y Despliegue PaaS (Render / Railway / Zeabur)
1. Ejecutar `Infrastructure/Adapters/Persistence/MySQL/Database/mysql/cloud_migration.sql` en la instancia MySQL gestionada (Aiven, Clever Cloud, Railway MySQL o TiDB Cloud).
2. Conectar el repositorio GitHub en **Render**, **Railway** o **Zeabur** seleccionando el `Dockerfile` de la raíz y definiendo las variables de entorno `PERSISTENCE_DRIVER=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` (o el manifiesto automático `render.yaml`).

---

## 6. Endpoints RESTful y Pruebas Automatizadas

| Método | Ruta | Caso de Uso CQRS | Códigos HTTP |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/health` | Estado de salud, driver activo, tarifa IVA y últimos eventos | `200` |
| `POST` | `/api/auth/login` | `AuthenticateUserUseCase` (`AuthenticateUserCommand`) | `200`, `401`, `422` |
| `GET` | `/api/users` | `GetAllUsersUseCase` (`GetAllUsersQuery`, filtro `?q=`) | `200` |
| `POST` | `/api/users` | `CreateUserUseCase` (`CreateUserCommand`) | `201`, `409`, `422` |
| `GET` | `/api/users/{id}` | `GetUserByIdUseCase` (`GetUserByIdQuery`) | `200`, `404` |
| `PUT` | `/api/users/{id}` | `UpdateUserUseCase` (`UpdateUserCommand`) | `200`, `404`, `409`, `422` |
| `DELETE` | `/api/users/{id}` | `DeleteUserUseCase` (`DeleteUserCommand`) | `200`, `404` |
| `GET` | `/api/gastos` | `GetAllGastosUseCase` (`GetAllGastosQuery`, `?q=` y `?usuario=`) | `200` |
| `POST` | `/api/gastos` | `CreateGastoUseCase` (`CreateGastoCommand`) | `201`, `422` |
| `GET` | `/api/gastos/{id}` | `GetGastoByIdUseCase` (`GetGastoByIdQuery`) | `200`, `404` |
| `PUT` | `/api/gastos/{id}` | `UpdateGastoUseCase` (`UpdateGastoCommand`) | `200`, `404`, `422` |
| `DELETE` | `/api/gastos/{id}` | `DeleteGastoUseCase` (`DeleteGastoCommand`) | `200`, `404` |

### Ejecución de la Suite Automatizada de Pruebas
```bash
php tests/run_tests.php
```
Valida objetos de valor, invariante contable de IVA en `GastoModel`, servicios CQRS de `Usuario` y `Gasto`, invalidación de caché, publicación de eventos de dominio y respuestas HTTP (`201`, `200`, `401`, `404`, `409`, `422`) de los controladores REST.
