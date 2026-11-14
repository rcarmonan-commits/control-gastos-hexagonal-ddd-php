<?php
declare(strict_types=1);

$gastos = $gastoController->list($_GET);
$flash = $_GET['msg'] ?? null;
$sumaSinIva = 0.0;
$sumaIva = 0.0;
$sumaConIva = 0.0;
foreach ($gastos as $item) {
    $sumaSinIva += $item->valorTotalSinIVA;
    $sumaIva += $item->ivaTotal;
    $sumaConIva += $item->valorTotalConIVA;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Gastos (#6) — Arquitectura Hexagonal, DDD y CQRS</title>
    <link rel="stylesheet" href="?asset=css">
</head>
<body>
<header class="topbar">
    <h1>Ejercicio #6: Gestion CRUDL de Gastos — Hexagonal + DDD + CQRS</h1>
    <nav>
        <a href="?action=gastos.list" class="active">Gastos (#6)</a>
        <a href="?action=gastos.search">Buscar Gasto</a>
        <a href="?action=users.list">Usuarios</a>
        <a href="/swagger" target="_blank">Swagger OpenAPI</a>
        <a href="?action=login">Acceso</a>
    </nav>
</header>
<main class="container">
    <section class="card">
        <div class="card-header">
            <div>
                <h2>Registro de Gastos Corporativos y Academicos</h2>
                <small>Persistencia activa: <strong><?= htmlspecialchars($di->activePersistenceDriver()) ?></strong> | Total acumulado con IVA: <strong>$<?= number_format($sumaConIva, 2) ?></strong></small>
            </div>
            <a class="btn" href="?action=gastos.create">+ Registrar Nuevo Gasto</a>
        </div>
        <?php if ($flash !== null): ?>
            <div class="alert alert-success"><?= htmlspecialchars((string) $flash) ?></div>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Lugar</th>
                    <th>Descripcion</th>
                    <th>Sin IVA</th>
                    <th>IVA Total</th>
                    <th>Total con IVA</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($gastos as $g): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($g->id) ?></strong></td>
                    <td><?= htmlspecialchars($g->fecha) ?></td>
                    <td><?= htmlspecialchars($g->nombreUsuario) ?></td>
                    <td><?= htmlspecialchars($g->lugar) ?></td>
                    <td><?= htmlspecialchars($g->descripcion) ?></td>
                    <td>$<?= number_format($g->valorTotalSinIVA, 2) ?></td>
                    <td>$<?= number_format($g->ivaTotal, 2) ?></td>
                    <td><strong>$<?= number_format($g->valorTotalConIVA, 2) ?></strong></td>
                    <td class="actions">
                        <a class="btn btn-secondary" href="?action=gastos.edit&amp;id=<?= urlencode($g->id) ?>">Editar</a>
                        <a class="btn btn-danger" href="?action=gastos.delete&amp;id=<?= urlencode($g->id) ?>">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
