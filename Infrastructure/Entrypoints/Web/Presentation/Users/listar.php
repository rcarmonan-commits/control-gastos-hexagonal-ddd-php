<?php
declare(strict_types=1);

$users = $userController->list($_GET);
$flash = $_GET['msg'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Usuarios — Hexagonal DDD</title>
    <link rel="stylesheet" href="?asset=css">
</head>
<body>
<header class="topbar">
    <h1>Sistema Hexagonal DDD + CQRS — Modulo Usuarios</h1>
    <nav>
        <a href="?action=gastos.list">Gastos (#6)</a>
        <a href="?action=users.list" class="active">Usuarios</a>
        <a href="?action=users.search">Buscar Usuario</a>
        <a href="/swagger" target="_blank">Swagger API</a>
        <a href="?action=login">Acceso</a>
    </nav>
</header>
<main class="container">
    <section class="card">
        <div class="card-header">
            <div>
                <h2>Gestion CRUDL de Usuarios</h2>
                <small>Driver activo: <strong><?= htmlspecialchars($di->activePersistenceDriver()) ?></strong></small>
            </div>
            <a class="btn" href="?action=users.create">+ Nuevo Usuario</a>
        </div>
        <?php if ($flash !== null): ?>
            <div class="alert alert-success"><?= htmlspecialchars((string) $flash) ?></div>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u->id) ?></td>
                    <td><?= htmlspecialchars($u->name) ?></td>
                    <td><?= htmlspecialchars($u->email) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($u->role) ?></span></td>
                    <td><span class="badge badge-success"><?= htmlspecialchars($u->status) ?></span></td>
                    <td class="actions">
                        <a class="btn btn-secondary" href="?action=users.edit&amp;id=<?= urlencode($u->id) ?>">Editar</a>
                        <a class="btn btn-danger" href="?action=users.delete&amp;id=<?= urlencode($u->id) ?>">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
