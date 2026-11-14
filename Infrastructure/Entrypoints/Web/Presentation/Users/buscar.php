<?php
declare(strict_types=1);

$q = trim((string) ($_GET['q'] ?? ''));
$results = $q !== '' ? $userController->list(array('q' => $q)) : array();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Usuario — Hexagonal DDD</title>
    <link rel="stylesheet" href="?asset=css">
</head>
<body>
<header class="topbar">
    <h1>Consulta CQRS de Usuarios</h1>
    <nav>
        <a href="?action=users.list">Listado Completo</a>
        <a href="?action=gastos.list">Gastos</a>
    </nav>
</header>
<main class="container">
    <section class="card">
        <form method="get" action="">
            <input type="hidden" name="action" value="users.search">
            <div class="form-group">
                <label for="q">Buscar por ID, nombre, correo o rol</label>
                <input type="text" id="q" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Ej: Rosary, admin@crud.com, ADMIN">
            </div>
            <button type="submit" class="btn">Ejecutar Consulta</button>
        </form>
        <?php if ($q !== ''): ?>
            <h3 style="margin: 1.25rem 0 0.75rem;">Resultados (<?= count($results) ?>)</h3>
            <table>
                <thead><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach ($results as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u->id) ?></td>
                        <td><?= htmlspecialchars($u->name) ?></td>
                        <td><?= htmlspecialchars($u->email) ?></td>
                        <td><?= htmlspecialchars($u->role) ?></td>
                        <td><?= htmlspecialchars($u->status) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
